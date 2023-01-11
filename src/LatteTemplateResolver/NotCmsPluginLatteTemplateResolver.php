<?php

declare(strict_types=1);

namespace Efabrica\PhpstanConfig\LatteTemplateResolver;

use Efabrica\Cms\Core\Plugin\PluginContainerInterface;
use Efabrica\PHPStanLatte\Collector\CollectedData\CollectedResolvedNode;
use Efabrica\PHPStanLatte\LatteContext\LatteContext;
use Efabrica\PHPStanLatte\LatteTemplateResolver\CustomLatteTemplateResolverInterface;
use Efabrica\PHPStanLatte\LatteTemplateResolver\LatteTemplateResolverResult;
use Efabrica\PHPStanLatte\Template\Template;
use Efabrica\PHPStanLatte\Template\Variable;
use Nette\Application\UI\Control;
use ReflectionClass;

final class NotCmsPluginLatteTemplateResolver implements CustomLatteTemplateResolverInterface
{
    private const CONTROL_CLASS = 'control_class';

    private const TEMPLATES = 'templates';

    private ?PluginContainerInterface $pluginContainer = null;

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

            $reflectionClass = new ReflectionClass($globalPlugin);
            $frontendControlClassProperty = $reflectionClass->getProperty('frontendControlClass');
            $frontendControlClassProperty->setAccessible(true);
            $frontendControlClassName = $frontendControlClassProperty->getValue($globalPlugin);

            $frontendControlClassReflection = new ReflectionClass($frontendControlClassName);
            $frontendControlClassFileName = $frontendControlClassReflection->getFileName();
            $controls[] = new CollectedResolvedNode(self::class, $frontendControlClassFileName, [
                self::CONTROL_CLASS => $frontendControlClassName,
                self::TEMPLATES => $templatePaths,
            ]);
        }
        return $controls;
    }

    public function resolve(CollectedResolvedNode $resolvedNode, LatteContext $latteContext): LatteTemplateResolverResult
    {
        $params = $resolvedNode->getParams();
        $controlClass = $params[self::CONTROL_CLASS];

        $variableFinder = $latteContext->variableFinder();
        $variables = $variableFinder->find($controlClass, 'render', 'preparePluginRender');

        $templates = $params[self::TEMPLATES];
        $templatePaths = [];
        foreach ($templates as $template) {
            $templatePaths[] = $template['path'];
        }
        $templatePathFinder = $latteContext->templatePathFinder();
        foreach ($templatePathFinder->find($controlClass, 'render') as $templatePath) {
            $templatePaths[] = $templatePath;
        }
        $templatePaths = array_filter($templatePaths);

        $result = new LatteTemplateResolverResult();
        foreach ($templatePaths as $templatePath) {
            $result->addTemplate(new Template(
                realpath($templatePath),
                $controlClass,
                'render',
                $variables,
                [],
                [],
                []
            ));
        }

        return $result;
    }
}
