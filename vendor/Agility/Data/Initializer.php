<?php

namespace Agility\Data;

use Agility\Environment;
use Agility\Logging;

	class Initializer {

		public $connectionCache = [];

		private $appEnvironment;
		private $dbConnectors = [];

		private static $_sharedInstance;

		private function __construct(Environment $appEnvironment) {
			$this->appEnvironment = $appEnvironment;
		}

		static function getSharedInstance(Environment $appEnvironment = null) {

			if (empty(self::$_sharedInstance)) {

				if ($appEnvironment === false) {
					throw new \Exception("Application environment not specified", 1);
				}
				self::$_sharedInstance = new Initializer($appEnvironment);

			}
			return self::$_sharedInstance;

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

				if (is_null($dbConnectorObj = Connector\ConnectionFactory::createInstance($dbConfig))) {

					Logging\Logger::log("Database initialization error: Unidentified adapter ".$dbConfig["adapter"]." found in database configuration.", Logging\Severity::Critical);
					return false;

				}

				try {
					$dbConnectorObj->connect($dbConfig);
				}
				catch (\Exception $e) {

					Logging\Logger::log("Database connection error: ".$connectionName." connection failed to initialze.".$e->getMessage(), Logging\Severity::Critical);
					return false;

				}

				// Store the DB connection object into the cache under connection name; if connection name is not specified, it would the integer index
				$this->connectionCache[$connectionName] = $dbConnectorObj;

			}

			return true;

		}

	}

?>