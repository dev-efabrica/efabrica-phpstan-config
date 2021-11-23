<?php

$phpStanPaths = [
    getcwd() . '/phpstan.neon',
    getcwd() . '/phpstan.php',
];

foreach ($phpStanPaths as $phpStanPath) {
    if (!file_exists($phpStanPath)) {
        continue;
    }

    return [
        'includes' => [
            $phpStanPath,
        ],
    ];
}

return [];
