<?php
	if(!defined('WP_UNINSTALL_PLUGIN')) {
		exit();
	}

	global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "tinyFeedback_URLs");
	$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "tinyFeedback_textual");
	$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "tinyFeedback_settings");
?>
