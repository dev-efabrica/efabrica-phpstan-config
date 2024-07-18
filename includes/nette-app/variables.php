<?php

$container = require 'bootstrap.php';
if ($container === null) {
    return [];
}
$autoVariableTemplateFactory = $container->getByType('Tomaj\Latte\AutoVariableTemplateFactory', false);

if (!$autoVariableTemplateFactory) {
    return [];
}

if (!method_exists($autoVariableTemplateFactory, 'getVariables')) {
    return [];
}

$variables = [];
foreach ($autoVariableTemplateFactory->getVariables() as $name => $variable) {
    if (is_object($variable)) {
        $variables[$name] = get_class($variable);
        continue;
    }
    $variables[$name] = gettype($variable);
}

if (!$variables) {
    return [];
}

return [
    'parameters' => [
        'latte' => [
            'globalVariables' => $variables,
        ],
    ],
];
