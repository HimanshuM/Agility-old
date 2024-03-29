<?php

namespace Agility\HTTP;

use Exception;
use Agility\Application;
use Agility\Configuration\Settings;
use Agility\HTTP\Response\Response;
use Agility\HTTP\Mime\MimeTypes;

	class Controller {

		protected $applicationEnvironment;
		protected $applicationDirectory;

		protected $settings;

		protected $request;
		protected $response;
		protected $session;
		protected $sessionName;
		protected $sessionOptions;
		protected $sessionAutoInit;

		protected $viewPath;
		protected $jsPath;
		protected $cssPath;

		protected $layout;

		private $_beforeSubscribers = [];
		private $_afterSubscribers = [];

		private $_class;

		private $_actionRendered = false;

		function __construct() {

			$app = Application::getApplicationInstance();

			$this->applicationEnvironment = $app->environment;
			$this->applicationDirectory = $app->applicationDir;

			$this->settings = Settings::getSharedInstance();

			$this->response = new Response;

			$qualifiedClassName = get_called_class();
			if (strpos($qualifiedClassName, "App\\Controllers") !== false) {
				$this->_class = substr($qualifiedClassName, strlen("App\\Controllers\\"));
			}
			else {
				$this->_class = (new \ReflectionClass($this))->getShortName();
			}

			$this->viewPath = (Application::getApplicationInstance())->getPath("viewsDir");
			$this->jsPath = (Application::getApplicationInstance())->getPath("jsDir");
			$this->cssPath = (Application::getApplicationInstance())->getPath("cssDir");

			$this->layout = "shared/base";

			$this->session = Session::getSharedInstance();

			$this->sessionName = false;
			$this->sessionOptions = false;
			$this->sessionAutoInit = false;

		}

		function beforeAction() {
			$this->setActionSubscribers("before", func_get_args());
		}

		function afterAction() {
			$this->setActionSubscribers("after", func_get_args());
		}

		function execute($action, $request) {

			if ($this->sessionAutoInit) {

				if ($this->sessionName !== false) {

					if (!is_array($this->sessionOptions)) {
						$this->sessionOptions = [];
					}
					$this->sessionOptions["name"] = $this->sessionName;

				}

				$this->session->init($this->sessionOptions);

			}

			$this->request = $request;

			$this->executeActionSubscribers("before", $action);
			$response = $this->$action();
			$this->executeActionSubscribers("after", $action);

			$this->render($this->_class."/".$action, $response);

		}

		function render($template, $data = null, $layout = true) {

			if ($this->_actionRendered) {
				return;
			}

			if (is_null($template)) {
				return;
			}

			$template = strtolower($template);

			$view = new Render\View($this->viewPath, $this->jsPath, $this->cssPath);

			if ($layout === true) {
				$view->setLayout($this->layout);
			}
			else if ($layout === false) {
				$view->setLayout(null);
			}
			else if (is_string($layout)) {
				$view->setLayout($layout);
			}
			else {
				throw new Exception("Possible values for layout are 'true', 'false' or layout path.", 1);
			}

			$view->setView($template);

			// Add controller to data
			$data["parent"] = $this;

			// Render the template
			$data = $view->render($data);

			$this->renderCustom(MimeTypes::Html, $data);

		}

		function renderCustom($mimeType, $data) {

			if ($this->_actionRendered) {
				return;
			}

			$this->_actionRendered = true;

			$this->response->setMimeType($mimeType);
			$this->response->setBody($data);
			$this->response->respond();

		}

		function render_404() {

			$this->_actionRendered = true;
			(Routing\RouteDispatch::getSharedInstance())->softRedirect(404);

		}

		function render_500() {

			$this->_actionRendered = true;
			(Routing\RouteDispatch::getSharedInstance())->softRedirect(500);

		}

		private function setActionSubscribers($trigger) {

			$subscribersInfo = func_get_arg(1);

			if (count($subscribersInfo) < 2) {
				throw new Exception("Too few arguements supplied to ".$trigger."Action(), 2 expected.", 1);
			}

			$actions = $subscribersInfo[0];

			if (count($subscribersInfo) == 2) {
				$subscribers = $subscribersInfo[1];
			}
			else {
				$subscribers = array_slice($subscribersInfo, 1);
			}

			if (is_array($actions)) {

				foreach ($actions as $action) {
					$this->addActionSubcribers($trigger, $action, $subscribers);
				}

			}
			else {
				$this->addActionSubcribers($trigger, $actions, $subscribers);
			}

		}

		private function addActionSubcribers($trigger, $action, $subscribers) {

			if($trigger == "before") {

				if (empty($this->_beforeSubscribers[$action])) {
					$this->_beforeSubscribers[$action] = [];
				}

				if (is_array($subscribers)) {
					$this->_beforeSubscribers[$action] = array_merge($subscribers);
				}
				else {
					$this->_beforeSubscribers[$action][] = $subscribers;
				}
			}
			else if($trigger == "after") {

				if (empty($this->_afterSubscribers[$action])) {
					$this->_afterSubscribers[$action] = [];
				}

				if (is_array($subscribers)) {
					$this->_afterSubscribers[$action] = array_merge($subscribers);
				}
				else {
					$this->_afterSubscribers[$action][] = $subscribers;
				}

			}

		}

		private function executeActionSubscribers($trigger, $action) {

			$methods = [];
			if ($trigger == "before") {

				if (!empty($this->_beforeSubscribers[$action])) {
					$methods = $this->_beforeSubscribers[$action];
				}

			}
			else {

				if (!empty($this->_beforeSubscribers[$action])) {
					$methods = $this->_beforeSubscribers[$action];
				}

			}

			foreach ($methods as $method) {
				$this->$method();
			}

		}

	}

?>