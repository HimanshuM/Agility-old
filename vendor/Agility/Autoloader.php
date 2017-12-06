<?php

	function frameworkAutoloader($class) {

		$path = str_replace("\\", "/", $class);
		if (file_exists(__DIR__."/../".$path.".php")) {
			require_once __DIR__."/../".$path.".php";
		}

	}

	function applicationAutoloader($class) {

		$path = str_replace("\\", "/", $class);
		if (file_exists(__DIR__."/../../".$path.".php")) {
			require_once __DIR__."/../../".$path.".php";
		}

	}

	spl_autoload_register("frameworkAutoloader");
	spl_autoload_register("applicationAutoloader");

?>