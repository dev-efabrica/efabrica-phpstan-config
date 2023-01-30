<?php

$container = require 'bootstrap.php';
return $container->getByType('Nette\Application\IPresenterFactory', false);
