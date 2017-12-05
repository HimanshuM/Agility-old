<?php

namespace Agility;

	class Application {

		protected $environment;

		protected $applicationDir;
		protected $documentRoot;
		protected $filePaths;
		protected $cliOnlyApp = false;

		protected $noDatabase = false;
		protected $pluginSystemDisabled = false;

		protected $settings;

		private $dbEngine;

		function __construct() {

			$this->setEnvironment();

			$this->setApplicationDirectory();

			$this->setFilePaths();

		}

		function configure($callback) {
			($callback->bindTo($this))();
		}

		function run() {
			Logging\Logger::log("Yay!!");
		}

		protected function initialize() {

			if (!$this->noDatabase) {
				$this->dbEngine = Data\Initializer::getSharedInstance($this->environment);
			}

			$this->setupPluginSystem();

			if ($this->setupDatabase() == false) {
				return false;
			}

			return true;

		}

		private function setEnvironment() {

			$environment = getenv("AGILITY_ENV");
			if (!empty($environment)) {

				$environment = strtolower($environment);
				if ($environment !== "development" && $environment !== "test" && $environment !== "production") {

					Logging\Logger::log("Application initialization error: Invalid application environment encountered. Assuming 'development' mode.", Logging\Severity::Critical);
					$environment = "development";

				}

			}
			else {

				Logging\Logger::log("Application initialization error: Invalid application environment encountered. Assuming 'development' mode.", Logging\Severity::Critical);
				$environment = "development";

			}

			if ($environment == "development") {
				$this->enrvironment = Environment::Development;
			}
			else if ($environment == "testing") {
				$this->environment = Environment::Testing;
			}
			else if ($environment == "production") {
				$this->environment = Environment::Production;
			}

		}

		private function setApplicationDirectory() {

			$this->documentRoot = getcwd();
			$documentRoot = explode("/", str_replace("\\", "/", $this->documentRoot));
			unset($documentRoot[count($documentRoot) - 1]);
			$this->applicationDir = implode("/", $documentRoot);

		}

		private function setFilePaths() {

			$this->filePaths = new Configuration\FilePaths;

			$this->filePaths->appDir = $this->applicationDir."/app";
			$this->filePaths->configDir = $this->applicationDir."/config";
			$this->filePaths->controllersDir = $this->filePaths->appDir."/controllers";
			$this->filePaths->modelsDir = $this->filePaths->appDir."/models";
			$this->filePaths->pluginsDir = $this->applicationDir."/plugins";
			$this->filePaths->viewsDir = $this->filePaths->appDir."/views";

			$this->filePaths->appFile = $this->filePaths->configDir."/app.php";
			$this->filePaths->dbFileJson = $this->filePaths->configDir."/db.json";
			$this->filePaths->dbFileYaml = $this->filePaths->configDir."/db.yaml";
			$this->filePaths->environmentFile = $this->filePaths->configDir."/environment.php";
			$this->filePaths->routesFile = $this->filePaths->configDir."/routes.php";

		}

		private function setupDatabase() {

			if ($this->noDatabase) {
				return true;
			}

			if (file_exists($this->filePaths->dbFileJson)) {
				return $this->dbEngine->readDatabaseConfig($this->filePaths->dbFileJson);
			}
			else if (file_exists($this->filePaths->dbFileYaml)) {
				return $this->dbEngine->readDatabaseConfig($this->filePaths->dbFileYaml, false);
			}
			else {
				Logging\Logger::log("Database initialization error: Database configuration file not found.", Logging\Severity::Notice);
			}

			return true;

		}

		private function setupPluginSystem() {

			Plugin\PluginSystem::$environment = $this->environment;

			if (!$this->pluginSystemDisabled) {
				Plugin\PluginSystem::setup($this->filePaths->pluginsDir);
			}

		}

	}

?>