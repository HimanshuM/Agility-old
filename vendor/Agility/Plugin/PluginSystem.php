<?php

namespace Agility\Plugin;

	class PluginSystem {

		public static $plugins = [];

		static function loadPlugin($path) {

			$pluginName = self::getPluginName($path);
			// Skip directories with trailing ".off" in the name
			if (stripos($pluginName, ".off") !== false) {
				return;
			}
			$filePath = $path."/".$pluginName.".php";
			if (file_exists($filePath)) {

				require_once($filePath);
				self::setupPlugin($pluginName);

			}
			else {
				\Agility\Logging\Logger::log("Plugin load error: plugin loader not found at path '$path'. Plugin will be skipped.", Agiltiy\Logging\Severity::Notice);
			}

		}

		static function setup($location) {

			$pluginDirs = glob($location."/*", GLOB_ONLYDIR);
			foreach ($pluginDirs as $plugin) {
				self::loadPlugin($plugin);
			}

		}

		private static function setupPlugin($pluginName) {

			if (!class_exists($pluginName, false)) {

				\Agility\Logging\Logger::log("Plugin initialization error: Could not initialize plugin '$pluginName'.", Agiltiy\Logging\Severity::Warning);
				return;

			}

			try {
				$pluginObject = new $pluginName;
			}
			catch (Exception $e) {

				\Agility\Logging\Logger::log("Plugin initialization error: Could not initialize plugin '$pluginName'.", Agiltiy\Logging\Severity::Warning);
				return;

			}

			self::$plugins[$pluginName] = $pluginObject;

		}

		private static function getPluginName($path) {

			$segmentedPath = explode("/", str_replace("\\", "/", $path));
			return array_pop($segmentedPath);

		}

	}

?>