<?php

declare(strict_types=1);

namespace Efabrica\PhpstanConfig\LatteTemplateResolver;

use Efabrica\Cms\Core\Plugin\PluginContainerInterface;
use Efabrica\PHPStanLatte\Collector\CollectedData\CollectedResolvedNode;
use Efabrica\PHPStanLatte\LatteContext\LatteContext;
use Efabrica\PHPStanLatte\LatteTemplateResolver\CustomLatteTemplateResolverInterface;
use Efabrica\PHPStanLatte\LatteTemplateResolver\LatteTemplateResolverResult;
use Efabrica\PHPStanLatte\Template\Component;
use Efabrica\PHPStanLatte\Template\ItemCombinator;
use Efabrica\PHPStanLatte\Template\Template;
use Efabrica\PHPStanLatte\Template\TemplateContext;
use Efabrica\PHPStanLatte\Template\Variable;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use ReflectionClass;

final class NotCmsMultiTemplatePluginLatteTemplateResolver implements CustomLatteTemplateResolverInterface
{
    private const CONTROL_CLASS = 'control_class';

    private const TEMPLATES = 'templates';

    private ?PluginContainerInterface $pluginContainer = null;

    /** @var Component[] */
    private array $globalPlugins = [];

    public function __construct(string $bootstrap)
    {
        if (file_exists($bootstrap)) {
            $container = require $bootstrap;
            $this->pluginContainer = $container->getByType('Efabrica\Cms\Core\Plugin\PluginContainerInterface', false);
        }
    }

    public function collect(): array
    {
        if ($this->pluginContainer === null) {
            return [];
        }

        $globalPlugins = $this->pluginContainer->getGlobalPlugins();
        $allPlugins = array_merge($globalPlugins, $this->pluginContainer->getPlugins());

        $controls = [];
        $allPluginComponents = [];
        foreach ($allPlugins as $identifier => $plugin) {
            if (!method_exists($plugin, 'getTemplates')) {
                continue;
            }

            $templatePaths = $plugin->getTemplates();

            $reflectionClass = new ReflectionClass($plugin);
            $frontendControlClassProperty = $reflectionClass->getProperty('frontendControlClass');
            $frontendControlClassProperty->setAccessible(true);
            $frontendControlClassName = $frontendControlClassProperty->getValue($plugin);

            $pluginComponent = new Component($identifier, new ObjectType($frontendControlClassName));

            if ($plugin->isGlobalPlugin()) {
                $this->globalPlugins[] = $pluginComponent;
            }

            $allPluginComponents[] = $pluginComponent;

            $frontendControlClassReflection = new ReflectionClass($frontendControlClassName);
            $frontendControlClassFileName = $frontendControlClassReflection->getFileName();
            $controls[] = new CollectedResolvedNode(self::class, $frontendControlClassFileName, [
                self::CONTROL_CLASS => $frontendControlClassName,
                self::TEMPLATES => $templatePaths,
            ]);
        }

        // second level of subcomponents
        foreach ($allPluginComponents as $pluginComponent) {
            $pluginComponent->addSubcomponents($this->globalPlugins);
        }

        return $controls;
    }

    public function resolve(CollectedResolvedNode $resolvedNode, LatteContext $latteContext): LatteTemplateResolverResult
    {
        $params = $resolvedNode->getParams();
        $controlClass = $params[self::CONTROL_CLASS];

        $variableFinder = $latteContext->variableFinder();
        $variables = $variableFinder->find($controlClass, 'render', 'preparePluginRender');

        // fields assigned in trait are not included... we have to hack it here
        $variables[] = new Variable('fields', new ArrayType(new StringType(), new MixedType()));

        $templatePaths = [];
        $templatePathFinder = $latteContext->templatePathFinder();
        foreach ($templatePathFinder->find($controlClass, 'render') as $templatePath) {
            $templatePath = realpath($templatePath);
            if ($templatePath) {
                $templatePaths[] = $templatePath;
            }
        }

        $templates = $params[self::TEMPLATES];
        foreach ($templates as $template) {
            $templatePath = realpath($template['path']);
            if ($templatePath) {
                $templatePaths[] = $templatePath;
            }
        }

        $templatePaths = array_unique(array_filter($templatePaths));


        $componentsFinder = $latteContext->componentFinder();
        $components = ItemCombinator::merge($componentsFinder->find($controlClass, 'init', 'beforeRender'), $this->globalPlugins);

        $templateContext = new TemplateContext($variables, $components, [], []);

        $result = new LatteTemplateResolverResult();
        foreach ($templatePaths as $templatePath) {
            $result->addTemplate(new Template(
                realpath($templatePath),
                $controlClass,
                'render',
                $templateContext
            ));
        }

        return $result;
    }
}
