<?php

namespace Agility;

use Agility\Configuration\Environment;
use Agility\Configuration\Settings;

	class Application {

		public $environment;
		public $applicationDir;
		public $settings;

		protected $documentRoot;
		protected $filePaths;
		protected $cliOnlyApp = false;

		protected $noDatabase = false;
		protected $pluginSystemDisabled = false;

		private $_dbEngine;
		private $_configuration;

		private static $_thisInstance;

		function __construct() {

			$this->settings = Settings::getSharedInstance();

			$this->setEnvironment();

			$this->setApplicationDirectory();

			$this->setFilePaths();

		}

		static function getApplicationInstance() {
			return self::$_thisInstance;
		}

		static function configure($callback) {
			($callback->bindTo(self::$_thisInstance))();
		}

		function isCli() {

			if (isset($_SERVER["argc"]) && is_numeric($_SERVER["argc"]) && (substr(PHP_SAPI, 0, 3) == "cli") && (substr(php_sapi_name(), 0, 3) == "cli")) {
				return true;
			}
			return false;

		}

		function getPath($id) {

			if (!isset($this->filePaths->$id)) {
				throw new Exception("No path present by identifier $id", 1);
			}

			return $this->filePaths->$id;

		}

		function run() {
			(HTTP\Request\RequestDispatch::getSharedInstance())->processRequest();
		}

		protected function initialize() {

			self::$_thisInstance = &$this;

			if ($this->setupDatabase() == false) {
				return false;
			}

			$this->setupErrorHandlerRoutine();

			$this->setupPluginSystem();

			$this->loadUserConfiguration();

			$this->loadCustomMimeTypes();

			$this->loadRoutes();

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
				$this->environment = new Environment(Environment::Development);
			}
			else if ($environment == "testing") {
				$this->environment = new Environment(Environment::Testing);
			}
			else if ($environment == "production") {
				$this->environment = new Environment(Environment::Production);
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
			$this->filePaths->jsDir = /*$this->filePaths->appDir.*/"/scripts";
			$this->filePaths->cssDir = /*$this->filePaths->appDir.*/"/stylesheets";

			$this->filePaths->appFile = $this->filePaths->configDir."/app.php";
			$this->filePaths->dbFileJson = $this->filePaths->configDir."/db.json";
			$this->filePaths->dbFileYaml = $this->filePaths->configDir."/db.yaml";
			$this->filePaths->environmentFile = $this->filePaths->configDir."/environment.php";
			$this->filePaths->routesFile = $this->filePaths->configDir."/routes.php";
			$this->filePaths->mimeTypesFile = $this->filePaths->configDir."/mime_types.php";

		}

		private function setupDatabase() {

			if ($this->noDatabase) {
				return true;
			}

			$this->_dbEngine = Data\Initializer::getSharedInstance($this->environment);

			if (file_exists($this->filePaths->dbFileJson)) {
				return $this->_dbEngine->readDatabaseConfig($this->filePaths->dbFileJson);
			}
			else if (file_exists($this->filePaths->dbFileYaml)) {
				return $this->_dbEngine->readDatabaseConfig($this->filePaths->dbFileYaml, false);
			}
			else {
				Logging\Logger::log("Database initialization error: Database configuration file not found.", Logging\Severity::Notice);
			}

			return true;

		}

		private function setupErrorHandlerRoutine() {
			HTTP\ErrorHandling\ErrorHandler::attachErrorRoutes();
		}

		private function setupPluginSystem() {

			Plugin\PluginSystem::$environment = $this->environment;

			if (!$this->pluginSystemDisabled) {
				Plugin\PluginSystem::setup($this->filePaths->pluginsDir);
			}

		}

		private function loadUserConfiguration() {

			if (file_exists($this->filePaths->appFile)) {
				require_once $this->filePaths->appFile;
			}

		}

		private function loadCustomMimeTypes() {

			if (file_exists($this->filePaths->mimeTypesFile)) {
				require_once $this->filePaths->mimeTypesFile;
			}

		}

		private function loadRoutes() {

			if (file_exists($this->filePaths->routesFile)) {
				require_once $this->filePaths->routesFile;
			}

		}

	}

?>