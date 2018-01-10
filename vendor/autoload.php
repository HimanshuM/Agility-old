<?php

(function () {

	$libDirs = glob(__DIR__."/*", GLOB_ONLYDIR);
	foreach ($libDirs as $library) {

		if (file_exists($library."/autoload.php")) {
			require_once $library."/autoload.php";
		}
		else if (file_exists($library."/autoloader.php")) {
			require_once $library."/autoloader.php";
		}

	}

})();