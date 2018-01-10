<?php

namespace Agility\Plugin;

use Exception;
use Agility\Logging\Severity;
use Agility\Logging\Logger;

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
				Logger::log("Plugin load error: plugin loader not found at path '$path'. Plugin will be skipped.", Severity::Notice);
			}

		}

		static function invokeDelayedMethods() {

			foreach (self::$plugins as $plugin) {
				$plugin->invokeDelayedMethod();
			}

		}

		private static function setupPlugin($pluginName) {

			if (!class_exists($pluginName, false) || get_parent_class($pluginName) !== "Agility\Plugin\Plugin") {

				Logger::log("Plugin initialization error: Could not initialize plugin '$pluginName'.", Severity::Warning);
				return;

			}

			try {
				$pluginObject = new $pluginName(self::$environment);
			}
			catch (Exception $e) {

				Logger::log("Plugin initialization error: Could not initialize plugin '$pluginName'.", Severity::Warning);
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