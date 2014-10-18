<?php
/**
 * Routing for Blog
 */

/*
 * Config
 */
if (isset($config['blog'])) {
	$default_config_blog = array(
		'limit_per_page' => 10,
	);
	$config['blog'] = array_merge($default_config_blog, $config['blog']);
}

/*
 * Routing
 */
$app->get('/blog/date/:value', function($value) use ($app, $config) {
	$page = $app->request()->get('page');
	$page = empty($page) ? 1 : $page;

	$json = new JsonFile(DATA_PATH . '/blog.json');
	$json->rsort('date');
	$pagination = $json->getPagination($page, $config['blog']['limit_per_page'], function($data) use ($value){
		return ($data['status'] === '公開' && strpos($data['date'], $value) === 0) ? true : false;
	});
	$data = array(
		'pagination' => $pagination,
		'filter' => array(
			'type' => 'date',
			'value' => $value
		)
	);
	$app->render('page/blog/index.html.twig', $data);
})->name('blog_list_date');

$app->get('/blog/:filter/:value', function($filter, $value) use ($app, $config) {
	$page = $app->request()->get('page');
	$page = empty($page) ? 1 : $page;

	$json = new JsonFile(DATA_PATH . '/blog.json');
	$json->rsort('date');
	$pagination = $json->getPagination($page, $config['blog']['limit_per_page'], function($data) use ($filter, $value){
		return ($data['status'] === '公開' && $data[$filter] === $value) ? true : false;
	});
	$data = array(
		'pagination' => $pagination,
		'filter' => array(
			'type' => $filter,
			'value' => $value
		)
	);
	$app->render('page/blog/index.html.twig', $data);
})->name('blog_list_filter');

$app->get('/blog/atom', function() use ($app) {
	$json = new JsonFile(DATA_PATH . '/blog.json');
	$json->rsort('date');
	$articles = $json->find_by_filter(function ($article) {
		return ($article['status'] === '公開') ? true : false;
	});
	$articles = array_slice($articles, 0, 10);

	$res = $app->response();
	$res['Content-Type'] = 'text/xml';

	$data = array(
		'articles' => $articles
	);
	$app->render('page/blog/index.atom.twig', $data);
});

$app->get('/blog/:id', function($id) use ($app) {
	$json = new JsonFile(DATA_PATH . '/blog.json');
	$post = $json->find($id);
	$data = array(
		'post' => $post
	);
	$app->render('page/blog/post.html.twig', $data);
})->name('blog_post');

$app->get('/blog', function() use ($app, $config) {
	$page = $app->request()->get('page');
	$page = empty($page) ? 1 : $page;

	$json = new JsonFile(DATA_PATH . '/blog.json');
	$json->rsort('date');
	$pagination = $json->getPagination($page, $config['blog']['limit_per_page'], function($data) {
		return ($data['status'] === '公開') ? true : false;
	});
	$data = array(
		'pagination' => $pagination
	);
	$app->render('page/blog/index.html.twig', $data);
});

