<?php
/**
 * Initialize Slim application with Twig.
 */

define('VERSION', '1.0.2');
define('CSRF_TOKEN_KEY', 'csrf_key');
define('VIEW_PATH', ROOT_PATH . '/view');
define('MODEL_PATH', ROOT_PATH . '/model');
define('SCHEMA_PATH', ROOT_PATH . '/schema');
define('DATA_PATH', ROOT_PATH . '/data');
define('IMAGE_PATH', ROOT_PATH . '/webroot/img');
define('TEMP_PATH', ROOT_PATH . '/tmp');
define('TWIG_CACHE_PATH', TEMP_PATH . '/cache');

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
	'cache' => TWIG_CACHE_PATH,
);
if (!isset($config['twig'])) {
	$config['twig'] = array();
}
$config['twig'] = array_merge($default_config_twig, $config['twig']);

/*
 * Instantiate a Slim application
 */
$app = new \Slim\Slim($config['slim']);

/*
 * Set Application-wide route conditions
 */
\Slim\Route::setDefaultConditions(array(
	'id' => '[0-9]+',
));

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
 * Extending Twig Function
 */
$function = new \Twig_SimpleFunction('get_json_file', function ($data_name, $status = null) {
	$json_path = DATA_PATH . "/${data_name}.json";
	$json = new JsonFile($json_path);

	if ($status) {
		$json->datas = array_filter($json->datas, function ($data) use ($status){
			return $data['status'] === $status;
		});
	}
	return $json;
});
$twig->addFunction($function);

$function = new \Twig_SimpleFunction('post_property_counts', function ($json) {
	return $json->property_counts();
});
$twig->addFunction($function);

$function = new \Twig_SimpleFunction('post_date_counts', function ($json, $yyyymm) {
	$datas = $json->find_by_filter(function($data) use ($yyyymm) {
		return (strpos($data['date'], $yyyymm) === 0) ? true : false;
	});

	$date_counts = array();
	foreach($datas as $data) {
		$date = $data['date'];
		if (!isset($date_counts[$date])) {
			$date_counts[$date] = 0;
		}
		$date_counts[$date]++;
	}

	return $date_counts;
});
$twig->addFunction($function);

$function = new \Twig_SimpleFunction('post_recent', function ($json, $row) {
	$json->rsort('date');
	return array_slice($json->datas, 0, $row);
});
$twig->addFunction($function);

$function = new \Twig_SimpleFunction('post_archives', function ($json, $row) {
	$archives = array();
	foreach($json->datas as $data) {
		$yyyymm = substr($data['date'], 0, 7);
		if (!isset($archives[$yyyymm])) {
			$archives[$yyyymm] = 0;
		}
		$archives[$yyyymm]++;
	}

	return $archives;
});
$twig->addFunction($function);

$function = new \Twig_SimpleFunction('load_rss', function ($url) {
	$feed = Feed::loadRss($url);
	return $feed->toArray();
});
$twig->addFunction($function);

/*
 * Twig Extension
 */
$twig->addExtension(new Twig_Extension_Debug);

use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\TokenParser\MarkdownTokenParser;
$engine = new MarkdownEngine\MichelfMarkdownEngine();
$twig->addExtension(new MarkdownExtension($engine));
$twig->addTokenParser(new MarkdownTokenParser($engine));

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
