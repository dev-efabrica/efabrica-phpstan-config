<?php

$container = require 'bootstrap.php';
if ($container === null) {
    return null;
}
return $container->getByType('Nette\Application\IPresenterFactory', false);
