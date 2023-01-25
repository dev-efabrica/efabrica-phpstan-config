<?php

declare(strict_types=1);

namespace Efabrica\PhpstanConfig\LatteContext\Collector\ComponentCollector;

use Efabrica\PhpstanConfig\NotCms\GlobalPluginsLoader;
use Efabrica\PHPStanLatte\LatteContext\CollectedData\CollectedComponent;
use Efabrica\PHPStanLatte\LatteContext\Collector\AbstractLatteContextCollector;
use Efabrica\PHPStanLatte\Resolver\NameResolver\NameResolver;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;

/**
 * @extends AbstractLatteContextCollector<Node, CollectedComponent>
 * @todo rewrite to component subcollector when it will be implemented
 */
final class GlobalPluginComponentCollector extends AbstractLatteContextCollector
{
    private GlobalPluginsLoader $globalPluginsLoader;

    public function __construct(GlobalPluginsLoader $globalPluginsLoader, NameResolver $nameResolver, ReflectionProvider $reflectionProvider)
    {
        parent::__construct($nameResolver, $reflectionProvider);
        $this->globalPluginsLoader = $globalPluginsLoader;
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function collectData(Node $node, Scope $scope): ?array
    {
        $className = $this->nameResolver->resolve($node->namespacedName);
        $classType = new ObjectType($className);

        if (!$classType->isInstanceOf('Efabrica\Cms\Core\Plugin\FrontendPluginControl')->yes()) {
            return null;
        }

        $components = [];
        foreach ($this->globalPluginsLoader->load() as $component) {
            $components[] = new CollectedComponent(
                $className,
                '',
                $component
            );
        }
        return count($components) > 0 ? $components : null;
    }
}
