<?php
	$available_pages = array(
		'statistics' => 'statistics.php',
		'configuration' => 'configuration.php',
		'written' => 'written-feedback.php',
		'about' => 'about.php'
	);
	$path = $available_pages['statistics'];
	if(isset($_GET['s']) && array_key_exists($_GET['s'], $available_pages)) {
		$path = $available_pages[$_GET['s']];
	} else {
		$_GET['s'] = 'statistics';
	}
?>
<div id="icon-edit-comments" class="icon32"></div>
<h2 class="nav-tab-wrapper"> 
	<a href="admin.php?page=tinyFeedback" class="nav-tab<?php if($_GET['s'] == 'statistics') { echo ' nav-tab-active'; } ?>">Statistics</a>
	<a href="admin.php?page=tinyFeedback&amp;s=written" class="nav-tab<?php if(isset($_GET['s']) && $_GET['s'] == 'written') { echo ' nav-tab-active'; } ?>">Written feedback</a>
	<a href="admin.php?page=tinyFeedback&amp;s=configuration" class="nav-tab<?php if(isset($_GET['s']) && $_GET['s'] == 'configuration') { echo ' nav-tab-active'; } ?>">Configuration</a>
	<a href="admin.php?page=tinyFeedback&amp;s=about" class="nav-tab<?php if(isset($_GET['s']) && $_GET['s'] == 'about') { echo ' nav-tab-active'; } ?>">About</a>
</h2>

<?php
	require_once($path);
?>
