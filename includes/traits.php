<?php

// $dir comes from nette-app/traits.php or nette-lib/traits.php

$path = getcwd() . '/' . $dir;
$fqcns = [];

// credits: https://stackoverflow.com/questions/22761554/how-to-get-all-class-names-inside-a-particular-namespace

$allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
$phpFiles = new RegexIterator($allFiles, '/\.php$/');
foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile->getRealPath());
    $tokens = token_get_all($content);
    $namespace = '';
    for ($index = 0; isset($tokens[$index]); $index++) {
        if (!isset($tokens[$index][0])) {
            continue;
        }
        if (T_NAMESPACE === $tokens[$index][0]) {
            $index += 2; // Skip namespace keyword and whitespace
            while (isset($tokens[$index]) && is_array($tokens[$index])) {
                $namespace .= $tokens[$index++][1];
            }
        }
        if (T_CLASS === $tokens[$index][0] && T_WHITESPACE === $tokens[$index + 1][0] && T_STRING === $tokens[$index + 2][0]) {
            $index += 2; // Skip class keyword and whitespace
            $fqcns[] = $namespace.'\\'.$tokens[$index][1];
            break;
        }
    }
}

$uses = [];
foreach ($fqcns as $fqcn) {
    if (!class_exists($fqcn)) {
        continue;
    }
    $classUses = class_uses($fqcn) ?: [];
    $uses = array_merge($uses, $classUses);
}

$traitPaths = [];
foreach ($uses as $use) {
    $reflectionClass = new ReflectionClass($use);
    $traitPaths[] = $reflectionClass->getFileName();
}

if ($traitPaths === []) {
    return [];
}

return [
    'parameters' => [
        'paths' => $traitPaths,
    ],
];
