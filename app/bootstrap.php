<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


// Load Nette Framework
require LIBS_DIR . '/../vendor/autoload.php';

umask(0);

// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode(array("178.143.15.169"));
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
//	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

$container->router[] = $frontRouter = new RouteList('Front');
$frontRouter[] = new Route('', 'Default:default');
$frontRouter[] = new Route('<year>-<month>', 'Default:show');

$container->router[] = $adminRouter = new RouteList('Admin');
$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', 'Default:default');

// Configure and run the application!
$container->application->run();
