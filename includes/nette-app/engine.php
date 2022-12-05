<?php

use Nette\Application\UI\TemplateFactory;

$container = require 'bootstrap.php';
return $container->getService('templateFactory')->createTemplate()->getLatte();
