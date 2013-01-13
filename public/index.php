<?php

// absolute filesystem path to this web root
define('WWW_DIR', __DIR__.'/system');

// check if we are on deploy or not
if (is_dir('/home/www-server/www-data/mochnak') === TRUE) {
	define('APP_DIR', '/home/www-server/www-data/mochnak/newsletter/current/app');
	define('LIBS_DIR', '/home/www-server/www-data/mochnak/newsletter/current/libs');
} else {
	define('APP_DIR', __DIR__.'/../app');
	define('LIBS_DIR', __DIR__.'/../libs');
}

// uncomment this line if you must temporarily take down your site for maintenance
// require APP_DIR . '/templates/maintenance.phtml';

// load bootstrap file
require APP_DIR . '/bootstrap.php';
