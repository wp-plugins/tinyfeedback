<h2>tinyFeedback Configuration</h2>
<p><em>Configure the behavior of the plugin as well as its form and tab appearance.</em></p>

<?php
	global $wpdb;
	/* Handle updates */
	if(isset($_POST['btn_submit'])) {
		$valid_fields = array();
		foreach($wpdb->get_results("SELECT name FROM " . $wpdb->prefix . "tinyFeedback_settings") as $item) {
			$valid_fields[] = $item->name;
		}

		$errors = '';
		foreach($_POST as $key=>$value) {
			if(in_array($key, $valid_fields)) {
				if(in_array($key, array('form_text', 'message_success', 'message_failure'))) {
					$value = wpautop($value);
				}
				if(false === $wpdb->update($wpdb->prefix . "tinyFeedback_settings", array('value' => str_replace(array("\r", "\r\n", "\n", "\t"), '', $value)), array('name' => $key))) {
					$errors = 'An error occurred while updating the configuration.';
				}
			}
		}
		if(empty($errors)) {
			echo '<div id="message" class="updated below-h2"><p>Your configuration has been successfully updated.</p></div>', PHP_EOL;
		} else {
			echo '<div id="message" class="error"><p>', $errors, '</p></div>', PHP_EOL;
		}
	}


	/* Fetch data */
	$settings = $wpdb->get_results("SELECT name, value FROM " . $wpdb->prefix . "tinyFeedback_settings", OBJECT_K);

	function yesnoRadio($setting) {
		echo '<input type="radio" id="' . $setting->name . '_yes" name="' . $setting->name . '" value="1"';
		if($setting->value) {
			echo ' checked';
		}
		echo '>', PHP_EOL;
		echo '<label for="' . $setting->name . '_yes">Yes</label>', PHP_EOL;
		echo '&nbsp;&nbsp;&nbsp;<input type="radio" id="' . $setting->name . '_no" name="' . $setting->name . '" value="0"';
		if(!$setting->value) {
			echo ' checked';
		}
		echo '>', PHP_EOL;
		echo '<label for="' . $setting->name . '_no">No</label>', PHP_EOL;
	}

	function textinput($setting) {
		echo '<input type="text" name="' . $setting->name . '" id="' . $setting->name . '" value="' . $setting->value . '">';
	}

	function editorinput($setting) {
		$conf = array(
			'textarea_name' => $setting->name,
			'media_buttons' => false,
			'textarea_rows' => 15,
			);
		wp_editor(stripslashes($setting->value), $setting->name, $conf);
	}
?>
	<h3>General settings</h3>
	<p><em>Settings concerning the plugin at large.</em></p>
	<form name="configuration" method="post">
		<input type="hidden" name="page" value="tinyFeedback">
		<input type="hidden" name="s" value="configuration">

		<table class="form-table">
			<tbody>
				<tr><th scope="row">Enable Google Analytics</th><td><?php yesnoRadio($settings['analytics_enabled']); ?></td><td><em>Tracks positive and negative clicks as <a href="http://code.google.com/apis/analytics/docs/tracking/eventTrackerOverview.html">events</a> in your Google Analytics account</em></td></tr>
				<tr><th scope="row">Insert CSS in header</th><td><?php yesnoRadio($settings['insert_css']); ?></td><td><em>Disable this if you want to manually embed the CSS in your primary stylesheet</em></td></tr>
				<tr><th scope="row">Utilize cookies</th><td><?php yesnoRadio($settings['cookie_enabled']); ?></td><td><em>Tab will only be displayed on pages where feedback has not been given (Inform your visitors!)</em></td></tr>
				<tr><th scope="row">Filter feedback through Akismet</th><td><?php yesnoRadio($settings['akismet_enabled']); ?></td><td><em>Spam protection (Requires the <a href="http://akismet.com/">Akismet</a> plugin enabled)</em></td></tr>
				<tr><th scope="row">Select style</th><td colspan="2">

				<?php 
					if($settings['insert_css']->value) { 
						$select = '<select name="current_style">' . PHP_EOL;
						if($handle = opendir(plugin_dir_path(__FILE__) . '../styles')) {
							while(false !== ($file = readdir($handle))) {
								if(substr($file, -8) != '-dev.css' && substr($file, -4) == '.css') {
									if($file == $settings['current_style']->value) {
										$select .= '<option selected value="' . $file . '">' . ucwords(str_replace('-', ' ', substr($file, 0, -4))) . '</option>' . PHP_EOL;
									} else {
										$select .= '<option value="' . $file . '">' . ucwords(str_replace('-', ' ', substr($file, 0, -4))) . '</option>' . PHP_EOL;
									}
								}
							}
							echo $select . '</select>' . PHP_EOL;
						} else {
							echo 'Error while reading available styles.';
						}
					} else { echo '<em>Manual CSS mode</em>'; } 
				?>
				
				</td></tr>
			</tbody>
		</table>

		<h3>Tab settings</h3>
		<p><em>The minimalistic tab petitioning visitor feedback.</em></p>
		<table class="form-table">
			<tbody>
				<tr><th scope="row">Feedback tab text</th><td width="200"><?php textinput($settings['widget_text']); ?></td><td><em></em></td></tr>
				<tr><th scope="row">Positive choice</th><td width="200"><?php textinput($settings['widget_yes']); ?></td><td><em></em></td></tr>
				<tr><th scope="row">Negative choice</th><td width="200"><?php textinput($settings['widget_no']); ?></td><td><em></em></td></tr>
				<tr><th scope="row">Feedback tab target</th><td width="200"><?php textinput($settings['widget_target']); ?></td><td><em>You may specify a target div or class for advanced customization. (Default: body)</em></td></tr>
			</tbody>
		</table>

		<h3>Form settings</h3>
		<p><em>This form asks the visitor for further information if the negative option is selected.</em></p>
		<table class="form-table">
			<tbody>
				<tr><th scope="row">E-mail placeholder</th><td><?php textinput($settings['form_email_placeholder']); ?></td><td><em></em></td></tr>
				<tr><th scope="row">Textarea placeholder</th><td><?php textinput($settings['form_textarea_placeholder']); ?></td><td><em></em></td></tr>
				<tr><th scope="row">Send button text</th><td><?php textinput($settings['form_send_button_text']); ?></td><td><em></em></td></tr>
				<tr><th scope="row">Left-side message</th><td><?php editorinput($settings['form_text']); ?></td><td><em></em></td></tr>
				<tr><th scope="row">Delivery success</th><td><?php editorinput($settings['written_success']); ?></td><td><em></em></td></tr>
				<tr><th scope="row">Delivery failure</th><td><?php editorinput($settings['written_failure']); ?></td><td><em></em></td></tr>
			</tbody>
		</table>
		<input type="submit" class="button-primary" value="Update settings" name="btn_submit">
	</form>
