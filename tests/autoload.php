<?php

$commands = [

];

if(isset($_ENV["RESET_ENVIRONMENT"]) and $_ENV["RESET_ENVIRONMENT"] == true) {
    foreach ($commands as $cmd) {
        $dir = __DIR__ . '/..';
        $cmd = sprintf($cmd, $dir);
        passthru($cmd);
    }
}

require_once __DIR__ . '/../vendor/autoload.php';
