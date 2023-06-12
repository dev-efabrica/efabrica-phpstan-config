<?php

$container = require 'bootstrap.php';
if ($container === null) {
    return null;
}
return $container->getByType('Symfony\Component\Console\Application', false);
