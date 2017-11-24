<?php

namespace Agility;

	class Application {

		protected $environment;

		protected $applicationDir;
		protected $filePaths;
		protected $cliOnlyApp;

		protected $settings;

		function __construct() {
			$this->initialize();
		}

		function run() {
			Logging\Logger::log("Yay!!");
		}

		private function initialize() {

			$this->setEnvironment();

			$this->setDocumentRoot();

			$this->setFilePaths();

			$this->setupDatabase();

			$this->loadUserConfiguration();

			$this->setupPluginSystem();

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

			$this->environment = $environment;

		}

		private function setDocumentRoot() {

			$documentRoot = explode("/", str_replace("\\", "/", getcwd()));
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

			if (file_exists($this->filePaths->dbFileJson)) {
				Data\Database::getSharedInstance($this->filePaths->dbFileJson, true);
			}
			else if (file_exists($this->filePaths->dbFileYaml)) {
				Data\Database::getSharedInstance($this->filePaths->dbFileYaml, false);
			}
			else {
				Logging\Logger::log("Database initialization error: Database configuration file not found.", Logging\Severity::Critical);
			}

		}

		private function loadUserConfiguration() {

			$this->settings = new Configuration\Settings;
			// Load application config file
			if (file_exists($this->filePaths->appFile)) {

				$temp["application"] = $this;
				extract($temp);
				require_once $this->filePaths->appFile;

				$application = null;
				$temp = null;

			}

		}

		private function setupPluginSystem() {
			Plugin\PluginSystem::setup($this->filePaths->pluginsDir);
		}

	}

?>