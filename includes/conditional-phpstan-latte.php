<?php

$phpStanLattePaths = [
    getcwd() . '/phpstan-latte.neon',
    getcwd() . '/phpstan-latte.php',
];

foreach ($phpStanLattePaths as $phpStanLattePath) {
    if (!file_exists($phpStanLattePath)) {
        continue;
    }

    return [
        'includes' => [
            $phpStanLattePath,
        ],
    ];
}

return [];
