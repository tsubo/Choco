<?php

/*
 * helper function
 */
function sitePath($resource_uri, $appName = 'default') {
	$app = \Slim\Slim::getInstance($appName);
	$req = $app->request();
	$log = $app->getLog();

	$root_uri = $req->getRootUri();
	$site_path = $root_uri . '/' . ltrim($resource_uri, '/');
	$log->debug("== Site Path: ${site_path}");

	return $site_path;
}

function file_count($target_dir, $file_name_pattern = "*.html.twig") {
	$count = 0;

	// search directories recursively
	$dirs = glob($target_dir . "/*", GLOB_ONLYDIR);
	foreach ($dirs as $dir) {
		$count += file_count($dir, $file_name_pattern);
	}

	// count files
	$files = glob($target_dir . '/' . $file_name_pattern);
	$count += count($files);

	return $count;
}

function file_list($target_dir) {
	$files = glob(rtrim($target_dir, '/') . '/*');
	$list = array();
	foreach ($files as $file) {
		if (is_file($file)) {
				$list[] = $file;
		}
		if (is_dir($file)) {
				$list = array_merge($list, file_list($file));
		}
	}

	return $list;
}

function uniq_filename($soruce_path) {
	if (!file_exists($soruce_path)) {
		return $soruce_path;
	}
	$dirname = pathinfo($soruce_path, PATHINFO_DIRNAME);
	$filename = pathinfo($soruce_path, PATHINFO_FILENAME);
	$ext = pathinfo($soruce_path, PATHINFO_EXTENSION);
	$cnt = 1;
	$path = "";
	while (true) {
		$path = $dirname . '/' . $filename . "_${cnt}." . $ext;
		if (!file_exists($path)) {
			break;
		}
		$cnt++;
	}
	return $path;
}