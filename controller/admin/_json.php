<?php
/**
 * Routings for /admin/json
 */

$app->get('/admin/json/', function() use ($app) {
	$app->render('page/404.html.twig');
});

// show json list
$app->get('/admin/json/:name', $authenticate($app), function($name) use ($app, $config) {
	$page = $app->request()->get('page');
	$page = empty($page) ? 1 : $page;

	$json = new JsonFile(DATA_PATH . "/${name}.json");
	$json->reverse();
	$pagination = $json->getPagination($page, $config['admin']['limit_per_page_json']);
	$data = array(
		'json_name' => $name,
		'pagination' => $pagination
	);
	$app->render('page/admin/json/index.html.twig', $data);
})->name('admin_json_list');

// show fillterd json list
$app->get('/admin/json/:name/:filter/:value', $authenticate($app), function($name, $filter, $value) use ($app, $config) {
	$page = $app->request()->get('page');
	$page = empty($page) ? 1 : $page;

	$json = new JsonFile(DATA_PATH . "/${name}.json");
	$json->reverse();
	$pagination = $json->getPagination($page, $config['admin']['limit_per_page_json'], function($data) use ($filter, $value) {
		return ($data[$filter] === $value) ? TRUE : FALSE;
	});
	$data = array(
		'json_name' => $name,
		'pagination' => $pagination,
		'filter' => array(
			'type' => $filter,
			'value' => $value
		)
	);
	$app->render('page/admin/json/index.html.twig', $data);
})->name('admin_json_list_filter');

// new json data
$app->get('/admin/json/:name/new', $authenticate($app), function($name) use ($app) {
	// get json schema
	$json_schema = file_get_contents(SCHEMA_PATH . "/${name}.schema.json");

	$default_data = array(
		"date" => date('Y-m-d'),
		"author" => $_SESSION['user'], 
		"status" => "公開",
	);
	$json_data = json_encode($default_data, JSON_PRETTY_PRINT);

	$data = array(
		'action' => $app->urlFor('admin_json_list', array('name' => $name)),
		'json_name' => $name,
		'json_schema' => $json_schema,
		'json_data' => $json_data,
	);
	$app->render('page/admin/json/data.html.twig', $data);
})->name('admin_json_new');

// create json data
$app->post('/admin/json/:name', $authenticate($app), function($name) use ($app) {
	$log = $app->getLog();
	$log->debug("== POST /admin/json/$name");
	$log->debug(print_r($app->request()->post(), true));

	$json_data = $app->request()->post('json_data');

	// TODO: validate
	// validator that can be shared the json-editor's schema file was not found.

	$json_array = json_decode($json_data, true);
	$json = new JsonFile(DATA_PATH . "/${name}.json");
	$json->add($json_array);
	if($json->save()) {
		$app->flash('success', '追加しました');
	} else {
		$app->flash('danger', 'ファイルの書込でエラーが発生しました');
	}
	$app->redirect(sitePath("admin/json/${name}"));
});

// edit json data
$app->get('/admin/json/:name/:id', $authenticate($app), function($name, $id) use ($app) {
	// get json data
	$json = new JsonFile(DATA_PATH . "/${name}.json");
	$json_data = \json_encode($json->find($id), \JSON_PRETTY_PRINT);

	// get json schema
	$json_schema = file_get_contents(SCHEMA_PATH . "/${name}.schema.json");

	$data = array(
		'method' => 'PUT',
		'action' => $app->urlFor('admin_json_data', array('name' => $name, 'id' => $id)),
		'json_name' => $name,
		'json_data' => $json_data,
		'json_schema' => $json_schema,
	);
	$app->render('page/admin/json/data.html.twig', $data);
})->name('admin_json_data');

// update json data
$app->put('/admin/json/:name/:id', $authenticate($app), function($name, $id) use ($app) {
	$log = $app->getLog();
	$log->debug("== PUT /admin/json/$name/$id");
	$log->debug(print_r($app->request()->put(), true));

	$json_data = $app->request()->post('json_data');

	// TODO: validate
	// validator that can be shared the json-editor's schema file was not found.

	$json_array = json_decode($json_data, true);
	$json = new JsonFile(DATA_PATH . "/${name}.json");
	$json->update($json_array);
	if($json->save()) {
		$app->flash('success', '更新しました');
	} else {
		$app->flash('danger', 'ファイルの書込でエラーが発生しました');
	}
	$app->redirect(sitePath("admin/json/${name}/${id}"));
});

// delete json data
$app->delete('/admin/json/:name/:id', $authenticate($app), function($name, $id) use ($app) {
	$json = new JsonFile(DATA_PATH . "/${name}.json");
	$json->delete(array($id));

	if($json->save()) {
		$app->flash('success', '削除しました');
	} else {
		$app->flash('danger', 'ファイルの書込でエラーが発生しました');
	}

	if (isset($_SESSION['prev_uri'])) {
		$app->redirect($_SESSION['prev_uri']);
	} else {
		$app->redirect(sitePath("admin/json/${name}"));
	}
});

// bulk delete json data
$app->delete('/admin/json/:name', $authenticate($app), function($name) use ($app) {
	$ids = $app->request->post('delete_items');

	if (!empty($ids)) {
		$json = new JsonFile(DATA_PATH . "/${name}.json");
		$json->delete($ids);

		if($json->save()) {
			$app->flash('success', '削除しました');
		} else {
			$app->flash('danger', 'ファイルの書込でエラーが発生しました');
		}
	}

	if (isset($_SESSION['prev_uri'])) {
		$app->redirect($_SESSION['prev_uri']);
	} else {
		$app->redirect(sitePath("admin/json/${name}"));
	}
});
