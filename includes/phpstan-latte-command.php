<?php

$commandParts = $_SERVER['argv'];

$phpstanCommand = '';

if (str_contains($commandParts[0], 'vendor/bin/phpstan')) {
    $phpstanCommand .= $commandParts[0] . ' analyse {dir}';

    for ($i = 1; $i < count($commandParts); $i++) {
        if (in_array($commandParts[$i], ['-c', '--configuration'], true)) {
            $phpstanCommand .= ' ' . $commandParts[$i] . ' ' . $commandParts[$i + 1];
        }
    }
}

if ($phpstanCommand === '') {
    return [];
}

return [
    'parameters' => [
        'latte' => [
            'features' => [
                'phpstanCommand' => $phpstanCommand,
            ],
        ],
    ],
];
