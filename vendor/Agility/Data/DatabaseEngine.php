<?php

namespace Agility\Data;

use Agility\Logging;

	class DatabaseEngine {

		private $appEnvironment;
		private $dbConnectors = [];

		private static $_sharedInstance;

		private function __construct($appEnvironment) {
			$this->appEnvironment = $appEnvironment;
		}

		static function getSharedInstance($appEnvironment = null) {

			if (empty(self::$_sharedInstance)) {

				if ($appEnvironment === false) {
					throw new \Exception("Application environment not specified", 1);
				}
				self::$_sharedInstance = new DatabaseEngine($appEnvironment);

			}
			return self::$_sharedInstance;

		}

		function registerDatabaseConnector(IDatabaseConnector $connectionObj) {
			$this->dbConnectors[$connectionObj->targetPlatform][] = $connectionObj;
		}

		private function readDatabaseConfig($configurationFile, $isJson = true) {

			if (($dbSettings = file_get_contents($configurationFile)) === false) {

				Logging\Logger::log("Database initialization error: Failed to read database configuration file.", Logging\Severity::Critical);
				return;

			}

			$dbSettingsArray;
			if ($isJson) {

				if (($dbSettingsArray = json_decode($dbSettings, true)) === false) {

					Logging\Logger::log("Database initialization error: Database configuration is not a valid JSON object.", Logging\Severity::Critical);
					return;

				}

			}
			else {
				// Process YAML file here...
			}

			return $this->processDatabaseConfig($dbSettingsArray);

		}

		private function processDatabaseConfig($dbSettingsArray) {

			$dbSettingsArray = $dbSettingsArray[$this->appEnvironment];
			foreach ($dbSettingsArray as $dbConfig) {

				if (empty($dbConnectors[$dbConfig["adapter"]])) {

					Logging\Logger::log("Database initialization error: Unidentified adapter ".$dbConfig["adapter"]." found in database configuration.", Logging\Severity::Critical);
					return false;

				}

				try {
					$dbConnectors[$dbConfig["adapter"]][0]->initiateConnection($dbConfig);
				}
				catch (\Exception $e) {

					Logging\Logger::log("Database connection error: ".$dbConnectors[$dbConfig["adapter"]][0]->name." failed to initialze.", Logging\Severity::Critical);
					return false;

				}

			}

			return true;

		}

	}

?>