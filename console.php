<?php

use Symfony\Component\Console\Application;

if (substr(php_sapi_name(), 0, 3) == 'cgi')
{
    die("You can run the FoolFrame console only from the command line.\n\n");
}

/**
 * Set error reporting and display errors settings.  You will want to change these when in production.
 */
error_reporting(-1);
ini_set('display_errors', 1);

/**
 * The path to the Composer vendor directory.
 */
define('VENDPATH', __DIR__.'/vendor'.DIRECTORY_SEPARATOR);

include VENDPATH.'autoload.php';
include __DIR__.'/utility/Console.php';

$application = new Application();
$application->add(new \Foolz\Basi\Console());
$application->run();