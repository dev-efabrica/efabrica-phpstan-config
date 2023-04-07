<?php

declare(strict_types=1);

namespace Efabrica\PhpstanConfig\NotCms;

use Efabrica\Cms\Core\Plugin\PluginContainerInterface;
use Efabrica\PHPStanLatte\Template\Component;
use PHPStan\Type\ObjectType;
use ReflectionClass;

final class GlobalPluginsLoader
{
    private ?PluginContainerInterface $pluginContainer = null;

    /** @var Component[]|null cache */
    private ?array $globalPlugins = null;

    public function __construct(string $bootstrap)
    {
        if (file_exists($bootstrap)) {
            $container = require $bootstrap;
            $this->pluginContainer = $container->getByType('Efabrica\Cms\Core\Plugin\PluginContainerInterface', false);
        }
    }

    /**
     * @return Component[]
     */
    public function load(): array
    {
        if ($this->pluginContainer === null) {
            return [];
        }

        if ($this->globalPlugins !== null) {
            return $this->globalPlugins;
        }

        $this->globalPlugins = [];
        foreach ($this->pluginContainer->getGlobalPlugins() as $globalPlugin) {
            $reflectionClass = new ReflectionClass($globalPlugin);

            $frontendControlClassProperty = $reflectionClass->getProperty('frontendControlClass');
            $frontendControlClassProperty->setAccessible(true);
            $frontendControlClassName = $frontendControlClassProperty->getValue($globalPlugin);

            $identifierClassProperty = $reflectionClass->getProperty('identifier');
            $identifierClassProperty->setAccessible(true);
            $identifier = $identifierClassProperty->getValue($globalPlugin);

            $this->globalPlugins[] = new Component($identifier, new ObjectType($frontendControlClassName));
        }

        // second level of subcomponents
        foreach ($this->globalPlugins as $globalPlugin) {
            $globalPlugin->addSubcomponents($this->globalPlugins);
        }

        return $this->globalPlugins;
    }
}
