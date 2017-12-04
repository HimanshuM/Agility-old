<?php

namespace Agility\Data;

use Agility\Logging;

	class Initializer {

		public $connectionCache = [];

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
				self::$_sharedInstance = new Initializer($appEnvironment);

			}
			return self::$_sharedInstance;

		}

		function registerDatabaseConnector(IDatabaseConnector $connectionObj) {

			if (is_array($connectionObj->targetPlatform)) {

				foreach ($connectionObj->targetPlatform as $target) {
					$this->dbConnectors[$target][] = $connectionObj;
				}

			}
			else {
				$this->dbConnectors[$connectionObj->targetPlatform][] = $connectionObj;
			}

		}

		function getDefaultConnectionIndex() {

			foreach ($this->connectionCache as $key => $value) {
				return $key;
			}

		}

		function getConnectorFromConnectionName($name) {

			return $this->connectionCache[$name];

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
			foreach ($dbSettingsArray as $connectionName => $dbConfig) {

				if (empty($this->dbConnectors[$dbConfig["adapter"]])) {

					Logging\Logger::log("Database initialization error: Unidentified adapter ".$dbConfig["adapter"]." found in database configuration.", Logging\Severity::Critical);
					return false;

				}

				$dbConnectorObj;

				try {

					$dbConnectorObj = $this->dbConnectors[$dbConfig["adapter"]][0];
					$dbConnectorObj->connect($dbConfig);

				}
				catch (\Exception $e) {

					Logging\Logger::log("Database connection error: ".$this->dbConnectors[$dbConfig["adapter"]][0]->name." failed to initialze.", Logging\Severity::Critical);
					return false;

				}

				// Store the DB connection object into the cache under connection name; if connection name is not specified, it would the integer index
				$this->connectionCache[$connectionName] = $dbConnectorObj;

			}

			return true;

		}

	}

?>