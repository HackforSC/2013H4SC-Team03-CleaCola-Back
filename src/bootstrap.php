<?php

$is_production = false;
$mode = 'development';
if (file_exists(realpath(dirname(__FILE__) . '/../src/') . '/is_production')) {
    // this file is created on deploy to mark production
    $is_production = true;
    $mode = 'production';
}

if ($is_production == false) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

session_start();

require '../vendor/autoload.php';

$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
    'handlers' => array(
        new \Monolog\Handler\FirePHPHandler(),
        new \Monolog\Handler\PushoverHandler('bmMozuphHpYIwFL2D4AezYulkqSaVa', '8DRkxqMVh6E2j6iZraTmTYzy30WwHS')
    ),
));

$app = new \Slim\Slim(array(
    'templates.path' => realpath(dirname(__FILE__) . '/../src/CC') . '/templates',
    'mode' => $mode,
    'debug' => true,
    'log.writer' => $logger,
    'cookies.lifetime' => '30 days'
));

if ($is_production == true) {
    $app->getLog()->setEnabled(true);
}

$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespaces(array(
    'CC' => realpath(dirname(__FILE__) . '/../src/')
));
$loader->register();

$yaml = new \Symfony\Component\Yaml\Parser();

try {
    $database_options = $yaml->parse(file_get_contents(dirname(__FILE__) . '/database.yml'));
} catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
    $app->getLog()->error('Could not parse database YML doc');
    exit();
}

$adapter = $database_options['environments'][$app->getMode()]['adapter'];
$host = $database_options['environments'][$app->getMode()]['host'];
$database = $database_options['environments'][$app->getMode()]['name'];
$user = $database_options['environments'][$app->getMode()]['user'];
$password = $database_options['environments'][$app->getMode()]['pass'];

// initialize database singleton
\CC\Helper\DB::instance(array(
    'dsn' => $adapter . ':host=' . $host . ';dbname=' . $database,
    'username' => $user,
    'password' => $password
));

require realpath(dirname(__FILE__) . '/../src/CC') . '/routes/site.php';
require realpath(dirname(__FILE__) . '/../src/CC') . '/routes/api.php';

$app->run();
