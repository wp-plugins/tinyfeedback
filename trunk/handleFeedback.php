<?php
	require_once('../../../wp-load.php'); // For accessing $wpdb
	global $wpdb;

	/* Try to check that we have received a good request */
	$site = $wpdb->get_row("SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name='siteurl'"); 
	$page = $_SERVER['HTTP_REFERER'];
	if(strpos($page, $site->option_value) === false) {
		die('Invalid referer');
	}
	$page = str_replace($site->option_value, '', $page);

	$config = array();
	$rows = $wpdb->get_results("SELECT name, value FROM " . $wpdb->prefix . "tinyFeedback_settings WHERE name IN ('akismet_enabled', 'cookie_enabled')", ARRAY_A);
	foreach($rows as $row) {
		$config[$row['name']] = (bool)$row['value'];
	}

	/* Handle positive feedback */
	if($_POST['type'] === 'positive') {
		$wpdb->query("INSERT INTO " . $wpdb->prefix . "tinyFeedback_URLs (url, positive_count, negative_count) VALUES ('$page', 1, 0) ON DUPLICATE KEY UPDATE positive_count=positive_count+1");
		if($config['cookie_enabled']) {
			updateCookie();
		}
	}

	/* Handle negative feedback */
	if($_POST['type'] === 'negative') {
		$wpdb->query("INSERT INTO " . $wpdb->prefix . "tinyFeedback_URLs (url, positive_count, negative_count) VALUES ('$page', 0, 1) ON DUPLICATE KEY UPDATE negative_count=negative_count+1");
		if($config['cookie_enabled']) {
			updateCookie();
		}
	}

	/* Handle written feedback */
	if($_POST['type'] === 'written') {
		$settings = $wpdb->get_row("SELECT written_success, written_failure FROM " . $wpdb->prefix . "tinyFeedback_settings LIMIT 1");
		$errors = '';
	
		if(!empty($_POST['email']) && !is_email($_POST['email'])) {
			$errors .= '<li>Malformed data in e-mail field</li>';
		}

		if(empty($_POST['feedback'])) {
			$errors .= '<li>You did not provide any feedback</li>';
		} else {
			/* Sanitize */
			$_POST['feedback'] = nl2br(wp_kses($_POST['feedback'], array()));
		}

		if($config['akismet_enabled']) {
			if(akismetCheck($_POST['feedback'], $_POST['email'])) {
				$errors .= '<li>Message marked as spam by <a href="http://akismet.com/">Akismet</a>.</li>'; 
			}
		}

		if(!empty($errors)) {
			die('<h2>Something went wrong...</h2><ul>' . $errors . '</ul>');
		}

		/* Fetch the page's URL id from DB */
		$urlID = $wpdb->get_row("SELECT id FROM " . $wpdb->prefix . "tinyFeedback_URLs WHERE url = '$page'");
		//SANITIZE DAMN IT!
		if($wpdb->query("INSERT INTO " . $wpdb->prefix . "tinyFeedback_textual (url_id, add_timestamp, author_email, message) VALUES (" . $urlID->id . ", now(), '$_POST[email]', '$_POST[feedback]')") === false) {
			$msg = $wpdb->get_row("SELECT value FROM " . $wpdb->prefix . "tinyFeedback_settings WHERE name='written_failure'");
			echo $msg->value;
		} else {
			$msg = $wpdb->get_row("SELECT value FROM " . $wpdb->prefix . "tinyFeedback_settings WHERE name='written_success'");
			echo $msg->value;
		}
	}

	function updateCookie() {
		$page = substr_replace($_SERVER['HTTP_REFERER'], '', 0, strlen($_SERVER['HTTP_ORIGIN'])) . ',';
		$prev = '';
		if(isset($_COOKIE['tinyFeedback'])) {
			$prev = $_COOKIE['tinyFeedback'];
			if(strpos($prev, $page) === false) {
				setcookie('tinyFeedback', ($prev . $page), time()+(3600*24*30), '/');
			}
		} else {
			setcookie('tinyFeedback', $page, time()+(3600*24*30), '/');
		}
	}

	function akismetCheck($msg, $email) {
		require_once( dirname(__FILE__) . '/../akismet/akismet.php');
		$key = akismet_get_key();

		$data = array(
			'blog' => $_SERVER['HTTP_ORIGIN'],
			'user_ip' => $_SERVER['REMOTE_ADDR'],
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'comment_content' => $msg);

		if(!empty($email)) {
			$data['comment_author_email'] = $email;
		}

		return akismet_comment_check($key, $data);
	}

	function akismet_comment_check($key, $data) {
		$request = '';
		$request =	'blog=' . urlencode($data['blog']) .
				'&user_ip=' . urlencode($data['user_ip']) . 
				'&user_agent=' . urlencode($data['user_agent']) . 
				'&comment_content=' . urlencode($data['comment_content']);
		if(isset($data['comment_author_email'])) {
			$request .= '&comment_author_email=' . urlencode($data['comment_author_email']);
		}

		$host = $http_host = $key . '.rest.akismet.com';
		$path = '/1.1/comment-check';
		$port = 80;
		$akismet_ua = 'Wordpress/3.1.1 | Akismet/2.5.3';
		$content_length = strlen($request);
		$http_request = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		if(false != ($fs = @fsockopen($http_host, $port, $errno, $errstr, 10))) {
			fwrite($fs, $http_request);
			while(!feof($fs)) {
				$response .= fgets($fs, 1160);
			}
			$response = explode("\r\n\r\n", $response, 2);
		}
		if('true' == $response[1]) {
			return true;
		}
		return false; 
	}

?>
