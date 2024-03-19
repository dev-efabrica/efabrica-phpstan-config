<?php

$phpStanPaths = [
    getcwd() . '/phpstan.neon',
    getcwd() . '/phpstan.php',
    getcwd() . '/phpstan.dist.neon',
    getcwd() . '/phpstan.dist.php',
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
