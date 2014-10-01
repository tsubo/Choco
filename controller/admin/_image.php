<?php
/**
 * Routings for /admin/image
 */

$app->get('/admin/image/', function() use ($app) {
	$app->render('page/404.html.twig');
});

// show image list
$app->get('/admin/image/:name', $authenticate($app), function($name) use ($app, $config) {
	$page = $app->request()->get('page');
	$page = empty($page) ? 1 : $page;

	$img_dir = new ImageDir(IMAGE_PATH . "/${name}");
	$pagination = $img_dir->getPagination($page, $config['admin']['limit_per_page_image']);

	$data = array(
		'image_name' => $name,
		'pagination' => $pagination,
	);
	$app->render('page/admin/image/index.html.twig', $data);
})->name('admin_image_list');

// upload image file
$app->post('/admin/image/:name', $authenticate($app), function($name) use ($app, $config) {
	$log = $app->getLog();
	$log->debug("== POST /admin/image/$name");
	$log->debug(print_r($app->request()->post(), true));
	$log->debug(print_r($_FILES, true));

	// upload
	$error_msg = "";
	$success_cnt = 0;
	$upload_files = $_FILES['upload_files'];
	$cnt = count($upload_files['tmp_name']);

	// protect from $_FILES Corruption Attack
	if (!isset($upload_files['error'])) {
		$error_msg .= 'パラメータが不正です<br>';
	} else {
		for($i = 0; $i < $cnt; $i++) {
			// validate
			if (!file_upload_validate($upload_files, $i, $error_msg, $config)) {
				continue;
			}
			// upload
			$uniq_filename = uniq_filename(image_file_path($name, basename($upload_files['name'][$i])));
			if (move_uploaded_file($upload_files['tmp_name'][$i], $uniq_filename)) {
				$success_cnt++;
			} else {
				$error_msg .= 'ファイル保存時にエラーが発生しました: ' . $upload_files['name'][$i] . '<br>';
			}
		}
	} 
	if($success_cnt > 0) {
		$app->flash('success', "${success_cnt}つのファイルをアップロードしました");
	}
	if(!empty($error_msg)) {
		$app->flash('danger', $error_msg);
	}
	$app->redirect(sitePath("admin/image/${name}"));
});

// bulk delete image file
$app->delete('/admin/image/:name', $authenticate($app), function($name) use ($app) {
	$log = $app->getLog();
	$log->debug("== DELETE /admin/image/$name");
	$log->debug(print_r($app->request()->post(), true));

	$files = $app->request->post('delete_files');
	$error_msg = "";
	$success_cnt = 0;
	foreach ($files as $file) {
		if (unlink(image_file_path($name, $file))) {
			$success_cnt++;
		} else {
			$error_msg .= "ファイルの削除に失敗しました: ${file}<br>";
		}	
	}

	if($success_cnt > 0) {
		$app->flash('success', "${success_cnt}つのファイルを削除しました");
	}
	if(!empty($error_msg)) {
		$app->flash('danger', $error_msg);
	}
	$redirect_path = $_SESSION['prev_uri'] ?: sitePath("admin/image/${name}");
	$app->redirect($redirect_path);
});

function image_file_path($subdir, $filename) {
	return IMAGE_PATH . '/' . $subdir . '/' . $filename;
}

function file_upload_validate($upload_files, $index, &$error_msg, $config) {
	// protect empty name
	if (empty($upload_files['name'][$index])) {
		return false;
	}
	// check upload error
	if (!upload_error_validate($upload_files['error'][$index], $upload_files['name'][$index], $error_msg)) {
		return false;
	}
	// check file size
	if ($upload_files['size'][$index] > $config['admin']['upload_max_file_size']) {
		$error_msg .= 'ファイルのサイズが大きすぎます: ' . $upload_files['name'][$index] . '<br>';
		return false;
	}
	// check mime type
	if (!mime_type_validate($upload_files['type'][$index])) {
		$error_msg .= 'アップロード出来ないファイル形式です: ' . $upload_files['name'][$index] . '<br>';
		return false;
	}
	return true;
}

function upload_error_validate($upload_error, $filename, &$error_msg) {
	$ret = true;
	switch ($upload_error) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_NO_FILE:
			$error_msg .= 'ファイルが選択されていません<br>';
			$ret = false;
			break;
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			$error_msg .= 'ファイルのサイズが大きすぎます: ' . $filename . '<br>';
			$ret = false;
			break;
		default:
			$error_msg .= 'その他のエラーが発生しました: ' . $filename . '<br>';
			$ret = false;
			break;
	}
	return $ret;
}

function mime_type_validate ($type) {
	$available_types = array(
		'gif' => 'image/gif',
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'pdf' => 'application/pdf'
	);
	if (!array_search($type, $available_types)) {
		return false;
	}
	return true;
}
