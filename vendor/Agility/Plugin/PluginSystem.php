<?php

namespace Agility\Plugin;

	class PluginSystem {

		public static $environment;
		public static $plugins = [];

		public static $location;

		static function setup($location) {

			self::$location = $location;

			$pluginDirs = glob($location."/*", GLOB_ONLYDIR);
			foreach ($pluginDirs as $plugin) {
				self::loadPlugin($plugin);
			}

		}

		static function loadPlugin($path) {

			$pluginInfo = self::getPluginName($path);
			// Skip directories with trailing ".off" in the name
			if (stripos($pluginInfo[0], ".off") !== false) {
				return;
			}
			$filePath = $path."/".$pluginInfo[0].".php";
			if (file_exists($filePath)) {

				require_once($filePath);
				self::setupPlugin($pluginInfo[1]);

			}
			else {
				\Agility\Logging\Logger::log("Plugin load error: plugin loader not found at path '$path'. Plugin will be skipped.", \Agility\Logging\Severity::Notice);
			}

		}

		private static function setupPlugin($pluginName) {

			if (!class_exists($pluginName, false) || get_parent_class($pluginName) !== "Agility\Plugin\Plugin") {

				\Agility\Logging\Logger::log("Plugin initialization error: Could not initialize plugin '$pluginName'.", \Agility\Logging\Severity::Warning);
				return;

			}

			try {
				$pluginObject = new $pluginName(self::$environment);
			}
			catch (Exception $e) {

				\Agility\Logging\Logger::log("Plugin initialization error: Could not initialize plugin '$pluginName'.", \Agility\Logging\Severity::Warning);
				return;

			}

			self::$plugins[$pluginName] = $pluginObject;

		}

		private static function getPluginName($path) {

			$pluginName = trim(substr($path, strlen(self::$location)), "/");

			$segmentedPath = explode("/", str_replace("\\", "/", self::$location));
			return [$pluginName, ucfirst(array_pop($segmentedPath)."\\".$pluginName."\\".$pluginName)];

		}

	}

?>