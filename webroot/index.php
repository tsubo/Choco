<?php 
/**
 * Choco CMS
 */
error_reporting(E_ALL|E_STRICT);

define('ROOT_PATH', dirname(dirname(__FILE__)));
define('CONTROLLER_PATH', ROOT_PATH . '/controller');

require_once ROOT_PATH . '/vendor/autoload.php';	// auto load 
require_once ROOT_PATH . '/model/JsonFile.php';
require_once ROOT_PATH . '/model/ImageDir.php';

// Controller
require_once CONTROLLER_PATH . '/init.php';					// Initialize Slim application with Twig.
require_once CONTROLLER_PATH . '/routing.php';			// Define your routing if you need.
require_once CONTROLLER_PATH . '/blog.php';					// Routing for blog
require_once CONTROLLER_PATH . '/mail.php';					// Routing for mail
require_once CONTROLLER_PATH . '/admin/admin.php';	// Routing for admin
require_once CONTROLLER_PATH . '/last.php';					// Default routing

// Run the Slim application.
$app->run();

