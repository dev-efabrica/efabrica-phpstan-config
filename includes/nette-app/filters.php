<?php

$container = require 'bootstrap.php';
$filterLoader = $container->getByType('Kelemen\Helper\Nette\FilterLoader', false);

if (!$filterLoader) {
    return [];
}

if (!method_exists($filterLoader, 'getFilters')) {
    return [];
}

$filters = [];
foreach ($filterLoader->getFilters() as $name => $filter) {
    if (is_string($filter)) {
        $filters[$name] = $filter;
        continue;
    }
    if (is_array($filter) && count($filter) === 2) {
        $class = $filter[0];
        if (is_object($class)) {
            $class = get_class($class);
        }
        $method = $filter[1];
        $filters[$name] = [$class, $method];
    }
}

return [
    'parameters' => [
        'latte' => [
            'filters' => $filters,
        ],
    ],
];
