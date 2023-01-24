<?php

declare(strict_types=1);

namespace Efabrica\PhpstanConfig\LatteContext\Collector;

use Efabrica\PHPStanLatte\LatteContext\CollectedData\CollectedComponent;
use Efabrica\PHPStanLatte\LatteContext\Collector\AbstractLatteContextCollector;
use Efabrica\PHPStanLatte\Resolver\NameResolver\NameResolver;
use Efabrica\PHPStanLatte\Resolver\ValueResolver\ValueResolver;
use Efabrica\PHPStanLatte\Template\Component;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Throwable;

/**
 * @extends AbstractLatteContextCollector<Node, CollectedComponent>
 * @todo rewrite to component subcollector when it will be implemented
 */
final class GridComponentCollector extends AbstractLatteContextCollector
{
    private ValueResolver $valueResolver;

    public function __construct(NameResolver $nameResolver, ReflectionProvider $reflectionProvider, ValueResolver $valueResolver)
    {
        parent::__construct($nameResolver, $reflectionProvider);
        $this->valueResolver = $valueResolver;
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function collectData(Node $node, Scope $scope): ?array
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return null;
        }

        if ($this->nameResolver->resolve($node->name) !== 'build') {
            return null;
        }

        $callerType = $scope->getType($node->var);
        if (!($callerType instanceof ObjectType && $callerType->isInstanceOf('Efabrica\Grid\Build\Definition\DataGridDefinition')->yes())) {
            return null;
        }

        $gridNameArg = $node->getArgs()[2] ?? null;
        $names = ['grid'];
        if ($gridNameArg !== null) {
            $names = $this->valueResolver->resolveStrings($gridNameArg->value, $scope) ?: [];
        }

        $components = [];
        foreach ($names as $name) {
            $components[] = new CollectedComponent(
                $classReflection->getName(),
                $scope->getFunctionName() ?: '',
                new Component($name, new ObjectType('Efabrica\Grid\Control\DataGridControl'))
            );
        }

        return count($components) > 0 ? $components : null;
    }
}
