<?php

global $bootstrap;

if ($bootstrap !== null) {
    return $bootstrap;
}

$bootstrapPath = getcwd() . '/app/bootstrap.php';
if (!file_exists($bootstrapPath)) {
    return null;
}
$bootstrap = require $bootstrapPath;
return $bootstrap;
