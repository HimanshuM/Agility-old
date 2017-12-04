<?php

namespace Plugins\DatabaseConnectors;

use Agility\Plugin\Plugin;

	class DatabaseConnectors extends Plugin {

		function __construct() {

			$this->name = "Database Connectors";
			$this->version = "0.1";

			$this->registerAutoloader();

			echo (new MysqlConnector\MysqlConnector)->targetPlatform;

		}

		function registerAutoloader() {

			spl_autoload_register(function ($class) {

				$namespace = "plugins\DatabaseConnectors";
				$path = substr(__DIR__, 0, strpos(__DIR__, $namespace)).$class.".php";

				if (file_exists($path)) {
					require_once $path;
				}

			});

		}

	}

?>