<?php
/*
	Plugin Name: tinyFeedback
	Plugin URI: http://cbsmth.se/web-development/tinyfeedback-wordpress-plugin/
	Description: An unobtrusive, simple yet highly configurable feedback plugin.
	Version: 1.2
	Author: Fredrik Karlström
	Author URI: http://cbsmth.se/
	Licence: GPL2

	---

	Copyright 2011  Fredrik Karlström  (email : cbsmth@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Do not expose information if called directly
if(!function_exists('add_action')) {
	if(isset($_GET['config'])) {
		require_once('../../../wp-load.php'); 
		global $wpdb;
		// Change this for name IN ...
		$rows = $wpdb->get_results("SELECT name, value FROM " . $wpdb->prefix . "tinyFeedback_settings WHERE name IN ('widget_text', 'widget_yes', 'widget_no', 'widget_target', 'widget_thankyou', 'form_textarea_placeholder', 'form_text', 'form_caption', 'form_email_placeholder', 'form_send_button_text', 'analytics_enabled', 'cookie_enabled')", ARRAY_A);
		$json = '';
		foreach($rows as $row) {
			$json .= '"' . $row['name'] . '": "' . $row['value'] . '",';
		}
		die('{' . substr($json, 0, -1) . '}');
	}
	die('You should not call me directly.');
}

// Installation
function install() {
	global $wpdb;
	// Settings table
	$table = $wpdb->prefix."tinyFeedback_settings";
	$structure = "CREATE TABLE $table (
		id int NOT NULL AUTO_INCREMENT,
		name varchar(64) NOT NULL,
		value text NOT NULL,
		UNIQUE KEY id (id),
		UNIQUE KEY name (name)
	);";
	$wpdb->query($structure);

	// Settings default values
	$default_settings = array(
		'widget_text' => 'Helpful?',
		'widget_yes' => 'Yes',
		'widget_no' => 'No',
		'widget_target' => 'body',
		'widget_thankyou' => 'Thank you for your input!',
		'form_text' => '<h2>Care to elaborate?</h2><p>Thank you, your input has been registered! Mind telling us what we could do better?</p><p>Fill out the form to the right, and we will do our best to take your opinions into consideration henceforth.</p><p><strong>Thank you for helping us improve!</strong><p><em>- Signature or team name here</em></p>',
		'form_caption' => 'Your Feedback',
		'form_email_placeholder' => 'Your e-mail (not required)',
		'form_textarea_placeholder' => 'Please tell us how to improve our service.',
		'form_send_button_text' => 'Send Feedback',
		'written_success' => '<h2>Feedback received!</h2><p>Your message has been received. Thank you for your assistance!</p>',
		'written_failure' => '<h2>An error has occurred</h2><p>We are terribly sorry about this mishap. Would you mind letting us know about this error by <a href="mailto:name@website.com">e-mail</a>?</p>',
		'analytics_enabled' => '0',
		'insert_jquery' => '1',
		'insert_css' => '1',
		'akismet_filter' => '0',
		'cookie_enabled' => '0',
		'current_style' => 'blue-vertical.css'
	);

	foreach($default_settings as $name => $setting) {
		$wpdb->query("INSERT INTO $table (name, value) VALUES ('" . $name . "', '" . $setting . "')");
	}

	// URL feedback table
	$table = $wpdb->prefix."tinyFeedback_URLs";
	$structure = "CREATE TABLE $table (
		id INT(9) NOT NULL AUTO_INCREMENT,
		url varchar(250) NOT NULL,
		positive_count INT(9) NOT NULL DEFAULT 0,
		negative_count INT(9) NOT NULL DEFAULT 0,
		UNIQUE KEY id (id),
		UNIQUE KEY url (url)
	);";
	$wpdb->query($structure);

	// Textual feedback table
	$table = $wpdb->prefix."tinyFeedback_textual";
	$structure = "CREATE TABLE $table (
		id INT(9) NOT NULL AUTO_INCREMENT,
		url_id INT(9) NOT NULL,
		author_email varchar(100),
		add_timestamp timestamp NOT NULL,
		replied bool NOT NULL DEFAULT 0,
		message text NOT NULL,
		UNIQUE KEY id (id),
		FOREIGN KEY (url_id) REFERENCES ".$wpdb->prefix."tinyFeedback_URLs(id) ON DELETE CASCADE ON UPDATE CASCADE
	);";
	$wpdb->query($structure);
}

/* -- Functionality -- */

function insertStyles() {
	global $wpdb;
	$config = $wpdb->get_results("SELECT value FROM " . $wpdb->prefix . "tinyFeedback_settings WHERE name IN ('insert_css', 'current_style')");
	if((bool)$config[1]->value) {
		echo '<link rel="stylesheet" href="' . plugin_dir_url(__FILE__) . 'styles/' . $config[0]->value . '">', PHP_EOL;
	}
}

function insertScripts() {
	global $wpdb;
	$config = $wpdb->get_row("SELECT value FROM " . $wpdb->prefix . "tinyFeedback_settings WHERE name = 'insert_jquery'");
	if((bool)$config->value) {
	//	echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js"></script>', PHP_EOL;
		wp_enqueue_script('jquery');
	}
	wp_enqueue_script('tinyfeedback', plugin_dir_url(__FILE__) . 'tinyFeedback.js');
	//echo '<script type="text/javascript" src="' . plugin_dir_url(__FILE__) . 'tinyFeedback.js"></script>', PHP_EOL;
}

function displayWidget() {
	echo '<script type="text/javascript"> jQuery(function() { tinyFeedback("' . plugin_dir_url(__FILE__) . '"); }); </script>', PHP_EOL;
}

function admin_overview() {
	require_once(dirname(__FILE__) . '/admin/admin.php');
}

function admin_actions() {
	add_menu_page('tinyFeedback', 'Feedback', 1, 'tinyFeedback', 'admin_overview', plugin_dir_url(__FILE__).'tinyFeedback.png', 30);
}

/* WordPress API Hooks */
add_action('activate_tinyFeedback/tinyFeedback.php', 'install');
add_action('wp_print_styles', 'insertStyles');
add_action('wp_print_scripts', 'insertScripts');
add_action('wp_footer', 'displayWidget');

// Include admin panel for administrators
if(is_admin()) {
	add_action('admin_menu', 'admin_actions');
}


?>
