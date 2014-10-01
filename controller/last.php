<?php
/**
 * Default routings
 */

// root
$app->get('/', function() use ($app) {
	$app->render('page/index.html.twig');
});

// wild card
// map resource UIRs to template file.
$app->get('/:param+', function($params) use ($app) {
	$log = $app->getLog();
	$log->debug("== Incomming wildcard route");

	$templates_dir =  $app->config('templates.path');
	$path = 'page/' . rtrim(join('/', $params), '/');
	$log->debug("path: ${path}");

	$exts = array('.html.twig', '/index.html.twig');
	foreach ($exts as $ext) {
		$absolute_path = $templates_dir . '/' . $path . $ext;
		$log->debug("absolute_path: ${absolute_path}");
		if (file_exists($absolute_path)) {
			$relative_path = '/' . $path . $ext;
			$app->render($relative_path);
			$log->debug("render: ${relative_path}");
			return;
		}
	}
	$app->pass();
});

// 404 Not Found
$app->notFound(function() use($app) {
	$app->render('page/404.html.twig');
});

