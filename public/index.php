<?php

// absolute filesystem path to this web root
define('WWW_DIR', __DIR__.'/system');

// absolute filesystem path to the application root
# define('APP_DIR', '/home/www-server/www-data/mochnak/newsletter');
define('APP_DIR', __DIR__.'/..');

// absolute filesystem path to the libraries
# define('LIBS_DIR', '/home/www-server/www-data/mochnak/libs');
define('LIBS_DIR', __DIR__.'/../libs');

// uncomment this line if you must temporarily take down your site for maintenance
// require APP_DIR . '/templates/maintenance.phtml';

// load bootstrap file
require APP_DIR . '/bootstrap.php';
