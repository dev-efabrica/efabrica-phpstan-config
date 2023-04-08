<?php

$container = require 'bootstrap.php';
if ($container === null) {
    return null;
}
return $container->getService('templateFactory')->createTemplate()->getLatte();
