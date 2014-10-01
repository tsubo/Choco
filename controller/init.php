<?php
/**
 * Initialize Slim application with Twig.
 */

define('VERSION', '1.0.0');
define('CSRF_TOKEN_KEY', 'csrf_key');
define('VIEW_PATH', ROOT_PATH . '/view');
define('MODEL_PATH', ROOT_PATH . '/model');
define('SCHEMA_PATH', ROOT_PATH . '/schema');
define('DATA_PATH', ROOT_PATH . '/data');
define('IMAGE_PATH', ROOT_PATH . '/webroot/img');

require_once(dirname(__FILE__) . '/_helper.php');
require_once(dirname(__FILE__) . '/CsrfGuard.php');

session_start();

/*
 * Config
 */
// choco config
require_once(ROOT_PATH . '/config.php');
$default_config = array(
	'webroot' => '',
	'site_title' => 'Site Title',
	'theme' => 'default',
);
$config = array_merge($default_config, $config);

// slim config
$default_config_slim = array(
	'view' => new \Slim\Views\Twig(),
	'templates.path' => VIEW_PATH,

	'debug' => false,
	'log.enabled' => false,
	'log.level' => \Slim\Log::DEBUG,
);
if (!isset($config['slim'])) {
	$config['slim'] = array();
}
$config['slim'] = array_merge($default_config_slim, $config['slim']);

// twig config
$default_config_twig = array(
	'debug' => true,
);
if (!isset($config['twig'])) {
	$config['twig'] = array();
}
$config['twig'] = array_merge($default_config_twig, $config['twig']);

/*
 * Instantiate a Slim application with Twig.
 */
$app = new \Slim\Slim($config['slim']);

// Set Twig options.
$view = $app->view();
$view->parserOptions = $config['twig'];

// Use Slim Views helper functions.
$view->parserExtensions = array(
	new \Slim\Views\TwigExtension()
);

/*
 * Add Twig templates directory.
 */
$twig = $app->view->getInstance();
$loader = $twig->getLoader();
if (!empty($config['theme'])) {
	$theme_dir = ROOT_PATH . '/webroot/theme/' . $config['theme'];
	$loader->addPath($theme_dir, 'theme');
}

/*
 * Set Application-wide route conditions
 */
\Slim\Route::setDefaultConditions(array(
	'id' => '[0-9]+',
));

/*
 * Set config to twig global valiables
 */
$req = $app->request;

$twig->addGlobal('_app', $app);															// app
$twig->addGlobal('_session', $_SESSION);										// session
$twig->addGlobal('_config', $config);												// config

// webroot_url
$webroot_url = $req->getUrl();
if (!empty($config['webroot'])) {
	$webroot_url .= $config['webroot'];
}
$twig->addGlobal('_webroot_url', $webroot_url);

/*
 * extension
 */
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\TokenParser\MarkdownTokenParser;

$engine = new MarkdownEngine\MichelfMarkdownEngine();
$twig->addExtension(new MarkdownExtension($engine));
$twig->addTokenParser(new MarkdownTokenParser($engine));

/*
 * middleware
 */
$app->add(new \Slim\Extras\Middleware\CsrfGuard(CSRF_TOKEN_KEY));

/*
 * hook
 */
$app->hook('slim.after.dispatch', function () {
	$_SESSION['prev_uri'] = \filter_input(\INPUT_SERVER, 'REQUEST_URI');
});

/*
 * for debug
 */
$log = $app->getLog();
$log->debug("== Start routing");
if ($req->getMethod() == 'POST' && $req->post('_METHOD')) {
	$log->debug("Method: " . $req->post('_METHOD'));
} else {
	$log->debug("Method: " . $req->getMethod());
}
$log->debug("getUrl: " . $req->getUrl());
$log->debug("getRootUri: " . $req->getRootUri());
$log->debug("getResoruceUri: " . $req->getResourceUri());
$log->debug("getPath: " . $req->getPath());
$log->debug("QUERY_STRING: " . \filter_input(\INPUT_SERVER, 'QUERY_STRING'));
$log->debug("REQUEST_URI: " . \filter_input(\INPUT_SERVER, 'REQUEST_URI'));
if (isset($_SERVER['HTTP_REFERER'])) {
	$log->debug("HTTP_REFERER: " . \filter_input(\INPUT_SERVER, 'HTTP_REFERER'));
}
$log->debug("webroot_url: " . $webroot_url);
if (isset($_SESSION['prev_uri'])) {
	$log->debug("prev_uri: " . $_SESSION['prev_uri']);
}
