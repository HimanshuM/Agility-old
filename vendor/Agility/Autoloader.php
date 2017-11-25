<?php

	function autoloader($class) {

		$path = str_replace("\\", "/", $class);
		try {
			require_once __DIR__."/../".$path.".php";
		}
		catch (Exception $e) {
			debug_print_backtrace();
		}

	}

	spl_autoload_register("autoloader");

	set_error_handler(function() { echo "<pre>"; debug_print_backtrace(); });

?>