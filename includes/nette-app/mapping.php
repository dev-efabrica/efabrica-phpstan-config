<?php

$container = require 'bootstrap.php';

return [
    'parameters' => [
        'latte' => [
            'applicationMapping' => $container->getParameters()['applicationMapping'] ?? [],
        ],
    ],
];
