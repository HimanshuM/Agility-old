<?php

	require_once "../vendor/autoload.php";

	require_once "../bin/Application.php";

	set_error_handler(function() { echo "<pre>"; debug_print_backtrace(); });

	$app = new Application;
	$app->run();

?>