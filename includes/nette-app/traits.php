<?php

$dir = 'app';

$container = require 'bootstrap.php';
if ($container === null) {
    return [];
}
return include __DIR__ . '/../traits.php';
