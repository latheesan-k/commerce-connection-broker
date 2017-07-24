<?php

use Phalcon\Loader;
use Phalcon\DI\FactoryDefault;
use Phalcon\Config\Adapter\Ini;
use EComConnBroker\Support\StartTime;
use Phalcon\Logger\Factory as Logger;
use EComConnBroker\Responses\JsonResponse;

// Record application start time
$start = microtime(true);

// Determine if we are in codeception test mode
$isTest = defined('TESTS_PATH');

// Define application directory globally
define('APP_DIR', $isTest
    ? TESTS_PATH . '../app/'
    : getcwd() . '/../app/');

// Attempt to load application configuration
$configFile = $isTest
    ? TESTS_PATH . 'config/settings.ini'
    : APP_DIR . 'config/settings.ini';
if (!file_exists($configFile))
    throw new Exception("Config file $configFile does not exists");
$config = new Ini($configFile);

// Configure application defaults
error_reporting(E_ALL);
ini_set('display_errors', $config->app->debug);
ini_set('display_startup_errors', $config->app->debug);
date_default_timezone_set($config->app->timezone);

// Register custom namespaces
(new Loader())->registerNamespaces([
    'EComConnBroker\Controllers' => APP_DIR . '/controllers/',
    'EComConnBroker\Exceptions' => APP_DIR . '/exceptions/',
    'EComConnBroker\Responses' => APP_DIR . '/responses/',
    'EComConnBroker\Support' => APP_DIR . '/support/'
])->register();

// Create a new di to share resources across the application
$di = new FactoryDefault();

// Register route collections dynamically
$di->set('collections', function() {
    return include(APP_DIR . 'routes/loader.php');
});

// Register singleton instance of the loaded config object
$di->setShared('config', function() use($config) {
    return $config;
});
$config = null;

// Register application start time
$di->setShared('start_time', function() use($start) {
    return new StartTime($start);
});
$start = null;

// Register singleton instance of the debug logger
$di->setShared('debug_logger', function() use($di) {
    $loggerConfig = $di->get('config')->logger;
    return Logger::load([
        'name' => APP_DIR . 'logs/debug_logs_' . date($loggerConfig->date_format) . '.txt',
        'adapter' => $loggerConfig->adapter
    ]);
});

// Register singleton instance of the error logger
$di->setShared('error_logger', function() use($di) {
    $loggerConfig = $di->get('config')->logger;
    return Logger::load([
        'name' => APP_DIR . 'logs/error_logs_' . date($loggerConfig->date_format) . '.txt',
        'adapter' => $loggerConfig->adapter
    ]);
});

// Register singleton instance of the json responder
$di->setShared('json_response', function() {
    return new JsonResponse();
});
