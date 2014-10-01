<?php

/*-----------------------------------------------------------------------------
 * environment
 *----------------------------------------------------------------------------*/
/* If you installed webroot directory in subdirectory,
 * define the path from Document Root to webroot directory. */
// $config['webroot'] = '/path/to/webroot';
// $config['webroot'] = '/webroot';

/*-----------------------------------------------------------------------------
 * site
 *----------------------------------------------------------------------------*/
$config['site_title'] = 'Choco - シンプルで拡張性の高い軽量CMS';

/*-----------------------------------------------------------------------------
 * theme
 *----------------------------------------------------------------------------*/
$config['theme'] = 'default';
// $config['theme'] = 'cerulean';
// $config['theme'] = 'cosmo';
// $config['theme'] = 'cyborg';
// $config['theme'] = 'darkly';
// $config['theme'] = 'flatly';
// $config['theme'] = 'journal';
// $config['theme'] = 'lumen';
// $config['theme'] = 'paper';
// $config['theme'] = 'readable';
// $config['theme'] = 'sandstone';
// $config['theme'] = 'simplex';
// $config['theme'] = 'slate';
// $config['theme'] = 'spacelab';
// $config['theme'] = 'superhero';
// $config['theme'] = 'united';
// $config['theme'] = 'yeti';

/*-----------------------------------------------------------------------------
 * blog
 *----------------------------------------------------------------------------*/
$config['blog'] = array(
	'title' => 'Blog',
	'subtitle' => 'Blogのサブタイトル',
	'limit_per_page' => 5,
);

/*-----------------------------------------------------------------------------
 * mail
 *----------------------------------------------------------------------------*/
$config['mail'] = array(
	'host' => 'smtp.gmail.com',
	'port' => 587,
	'encryption' => 'tls',
	'user' => '..SMTP_USER..@gmail.com',
	'password' => 'PASSWORD',
	'from' => '..FROM..@gmail.com',
	'to' => '..TO..@gmail.com',
);

/*-----------------------------------------------------------------------------
 * admin
 *----------------------------------------------------------------------------*/
$config['admin'] = array(
	'users' => array (
		// 'user_id' => 'passowrd',
		'user1' => 'pass1',
		'user2' => 'pass2',
	),
	'limit_per_page_json' => 10,
	'limit_per_page_image' => 12,
	'upload_max_file_size' => 800000,
);

/*-----------------------------------------------------------------------------
 * Slim Framework
 *----------------------------------------------------------------------------*/
$config['slim'] = array(
	'debug' => true,
	'log.enabled' => true,
);	

/*-----------------------------------------------------------------------------
 * Twig Template
 *----------------------------------------------------------------------------*/
$config['twig'] = array(
	'debug' => true,
);	
