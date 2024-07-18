<?php

declare(strict_types=1);

namespace Efabrica\PhpstanConfig\LatteContext\Collector\TemplatePathCollector;

use Efabrica\PHPStanLatte\LatteContext\Collector\AbstractLatteContextSubCollector;
use Efabrica\PHPStanLatte\LatteContext\Collector\TemplatePathCollector\TemplatePathCollectorInterface;
use Efabrica\PHPStanLatte\Resolver\ValueResolver\ValueResolver;
use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;

final class PluginTemplateFilePathCollector extends AbstractLatteContextSubCollector implements TemplatePathCollectorInterface
{
    private ValueResolver $valueResolver;

    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }

    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_$node
     */
    public function collect(Node $node, Scope $scope): ?array
    {
        if ($scope->getFunctionName() !== 'templateFilePath') {
            return [];
        }

        return $this->valueResolver->resolveStrings($node->expr, $scope);
    }
}
