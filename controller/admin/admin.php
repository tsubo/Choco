<?php
/**
 * Routings for Admin
 */

/*
 * Config
 */
if (isset($config['admin'])) {
	$default_config_admin = array(
		'users' => array(),
		'limit_per_page_json' => 10,
		'limit_per_page_image' => 12,
		'upload_max_file_size' => 1000000,
	);
	$config['admin'] = array_merge($default_config_admin, $config['admin']);
}

// route middleware
$authenticate = function($app) {
	return function() use ($app) {
		if(!isset($_SESSION['user'])) {
			$app->flash('danger', 'ログインしてください');
			$app->redirect(sitePath('admin/login'));
		}
	};
};
	
// login
$app->get('/admin/login', function() use ($app) {
	if(isset($_SESSION['user'])) {
		$app->redirect(sitePath('admin'));
		return;
	}
	$app->render('page/admin/login.html.twig');
});

// create login session
$app->post('/admin/login', function() use ($app, $config) {
	$postVars = $app->request->post();

	$log = $app->getLog();
	$log->debug("== POST Login");

	foreach($config['admin']['users'] as $user => $password) {
		if($postVars['user'] === $user && $postVars['password'] === $password) {
			$_SESSION['user'] = $user;
//			$log->debug("session_id(before): " . session_id());
			session_regenerate_id(true);
//			$log->debug("session_id( after): " . session_id());
			$app->redirect(sitePath('admin'));
			return;
		}
	}
	$app->flash('danger', 'User または Password が違います');
	$app->redirect(sitePath('admin/login'));
});

// logout
$app->get('/admin/logout', function() use ($app) {
	if (isset($_SESSION['user'])) {
		unset($_SESSION['user']);
		$app->flash('success', 'ログアウトしました');
	}
	$app->redirect(sitePath('admin/login'));
});

// show dashboard
$app->get('/admin/', $authenticate($app), function() use ($app) {
	// page count
	$page_path = VIEW_PATH . '/page';
	$page_count = file_count($page_path);
	$skeleton_page_count = file_count($page_path, "_SKELETON*.html.twig");
	$admin_page_count = file_count($page_path . '/admin');

	// used disk
	$used_disk = 0;
	$files = file_list(ROOT_PATH);
	foreach ($files as $file) {
		$used_disk += filesize($file);
	}
	$used_disk = round($used_disk / 1000000, 2);	// convert MB
	
	$data = array(
		'ver' => VERSION,
		'page_count' => $page_count - $admin_page_count - $skeleton_page_count,
		'used_disk' => $used_disk,
	);
	$app->render('page/admin/index.html.twig', $data);
});

/*
 * Extending Twig Function
 */
$function = new \Twig_SimpleFunction('json_data_counts', function () {
	$schema_files = glob(SCHEMA_PATH . "/*.schema.json");
	$schema_names = array_map(function ($path) {
		return basename($path, '.schema.json');
	}, $schema_files);

	$json_counts = array();
	foreach ($schema_names as $schema_name){
		$json_path = DATA_PATH . "/${schema_name}.json";
		$json = new JsonFile($json_path);
		$count = count($json->datas);
		$json_counts[$schema_name] = $count;
	}
	return $json_counts; 
});
$twig->addFunction($function);

$function = new \Twig_SimpleFunction('image_counts', function () {
	$image_dirs = glob(IMAGE_PATH . "/*", GLOB_ONLYDIR);
	$image_counts = array();
	foreach ($image_dirs as $path){
		$image_files = glob($path . "/*.*");
		$image_counts[basename($path)] = count($image_files);
	}
	return $image_counts;
});
$twig->addFunction($function);

$function = new \Twig_SimpleFunction('json_property_counts', function ($name) {
	$json_path = DATA_PATH . "/${name}.json";
	$json = new JsonFile($json_path);

	return $json->property_counts(); 
});
$twig->addFunction($function);

/*
 * Routings for Sub section
 */
require_once(dirname(__FILE__) . '/_json.php');
require_once(dirname(__FILE__) . '/_image.php');
