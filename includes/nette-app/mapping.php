<?php

$container = require 'bootstrap.php';
if ($container === null) {
    return [];
}
return [
    'parameters' => [
        'latte' => [
            'applicationMapping' => $container->getParameters()['applicationMapping'] ?? [],
        ],
    ],
];
