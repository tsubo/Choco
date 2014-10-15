<?php
/**
 * Routing for Mail
 */

/*
 * Config
 */
if (isset($config['mail'])) {
	$default_config_mail = array();
	$config['mail'] = array_merge($default_config_mail, $config['mail']);
}

/*
 * Routing
 */
$app->post('/mail', function() use ($app, $config) {
	$log = $app->getLog();
	$log->debug("== POST /mail");
	$log->debug(print_r($app->request()->post(), true));

	$post = $app->request()->post();
	$conf = $config['mail'];
	$encryption = $conf['encryption'] ? $conf['encryption'] : null;
	$log->debug(print_r($conf, true));

	$transport = \Swift_SmtpTransport::newInstance($conf['host'], $conf['port'], $encryption)
		->setUsername($conf['user'])
		->setPassword($conf['password']);
	$mailer = \Swift_Mailer::newInstance($transport);

	$body = post2body($post);
	$log->debug($body);

	try	{
		$message = \Swift_Message::newInstance()
			->setSubject($post['subject'])
			->setFrom($conf['from'])
			->setTo($conf['to'])
			->setReplyTo($post['email'])
			->setBody($body);
		$result = $mailer->send($message);

		if ($result > 0) {
			$app->flash('success', "送信しました");
		} else {
			$app->flash('danger', "送信エラーが発生しました");
		}
	} catch (Exception $e) {
		$app->flash('danger', "送信エラーが発生しました: " . $e->getMessage());
	}

	if (empty($post['mail_form'])) {
		$redirect_path = $_SESSION['prev_uri'] ?: sitePath("/");
		$app->redirect($redirect_path);
	} else {
		$app->render($post['mail_form']);
	}
});

function post2body($post) {
	$body = "";
	foreach ($post as $key => $value) {
		if ($key == CSRF_TOKEN_KEY || $key == 'mail_form') {
			continue;
		}
		$body .= "[$key]\n";
		$body .= "$value\n\n";
	}
	return $body;
}
