<?php

	function autoloader($class) {

		$path = str_replace("\\", "/", $class);
		require_once __DIR__."/../".$path.".php";

	}

	spl_autoload_register("autoloader");

?>