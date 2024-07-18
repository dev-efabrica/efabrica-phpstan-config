<?php

$dir = 'src';

$autoloadPath = getcwd() . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    return [];
}
require $autoloadPath;

return include __DIR__ . '/../traits.php';
