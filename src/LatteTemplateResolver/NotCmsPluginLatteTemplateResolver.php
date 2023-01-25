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
use PHPStan\Type\ObjectType;
use ReflectionClass;


// TODO: rename to multitemplate plugins
final class NotCmsPluginLatteTemplateResolver implements CustomLatteTemplateResolverInterface
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

        $controls = [];
        foreach ($this->pluginContainer->getGlobalPlugins() as $globalPlugin) {
            $templatePaths = [];
            if (method_exists($globalPlugin, 'getTemplates')) {
                $templatePaths = $globalPlugin->getTemplates();
            }


            // TODO: if not multitemplate, continue


            $reflectionClass = new ReflectionClass($globalPlugin);

            $frontendControlClassProperty = $reflectionClass->getProperty('frontendControlClass');
            $frontendControlClassProperty->setAccessible(true);
            $frontendControlClassName = $frontendControlClassProperty->getValue($globalPlugin);

            $identifierClassProperty = $reflectionClass->getProperty('identifier');
            $identifierClassProperty->setAccessible(true);
            $identifier = $identifierClassProperty->getValue($globalPlugin);

            var_dump($identifier);
            var_dump($frontendControlClassName);

            $this->globalPlugins[] = new Component($identifier, new ObjectType($frontendControlClassName));

            $frontendControlClassReflection = new ReflectionClass($frontendControlClassName);
            $frontendControlClassFileName = $frontendControlClassReflection->getFileName();
            $controls[] = new CollectedResolvedNode(self::class, $frontendControlClassFileName, [
                self::CONTROL_CLASS => $frontendControlClassName,
                self::TEMPLATES => $templatePaths,
            ]);
        }

        // second level of subcomponents
        foreach ($this->globalPlugins as $globalPlugin) {
            $globalPlugin->setSubcomponents($this->globalPlugins);
        }

        return $controls;
    }

    public function resolve(CollectedResolvedNode $resolvedNode, LatteContext $latteContext): LatteTemplateResolverResult
    {
        $params = $resolvedNode->getParams();
        $controlClass = $params[self::CONTROL_CLASS];

        $variableFinder = $latteContext->variableFinder();
        $variables = $variableFinder->find($controlClass, 'render', 'preparePluginRender');

        $templatePathFinder = $latteContext->templatePathFinder();
        $templatePaths = $templatePathFinder->find($controlClass, 'render');

        $templates = $params[self::TEMPLATES];
        foreach ($templates as $template) {
            $templatePaths[] = $template['path'];
        }
        $templatePathFinder = $latteContext->templatePathFinder();
        foreach ($templatePathFinder->find($controlClass, 'render') as $templatePath) {
            $templatePaths[] = $templatePath;
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
