<?php

$symfonyConsoleApplicationLoader = __DIR__ . '/symfony_console_application_loader.php';
if ($symfonyConsoleApplicationLoader === null) {
    return [];
}

return [
    'parameters' => [
        'symfony' => [
            'consoleApplicationLoader' => $symfonyConsoleApplicationLoader,
        ],
    ],
];
