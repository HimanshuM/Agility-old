<?php

namespace Agility\HTTP;

use Agility\Application;
use Agility\HTTP\Response\Response;
use Agility\HTTP\Mime\MimeTypes;

	class Controller {

		protected $applicationEnvironment;
		protected $applicationDirectory;

		protected $request;
		protected $response;

		protected $viewPath;

		private $_beforeSubscribers = [];
		private $_afterSubscribers = [];

		private $_class;

		private $_actionRendered = false;

		function __construct() {

			$app = Application::getApplicationInstance();

			$this->applicationEnvironment = $app->environment;
			$this->applicationDirectory = $app->applicationDir;

			$this->response = new Response;

			$qualifiedClassName = get_called_class();
			if (strpos($qualifiedClassName, "App\\Controllers") !== false) {
				$this->_class = substr($qualifiedClassName, strlen("App\\Controllers\\"));
			}
			else {
				$this->_class = (new \ReflectionClass($this))->getShortName();
			}

			$this->viewPath = (Application::getApplicationInstance())->getPath("viewsDir");

		}

		function beforeAction() {
			$this->setActionSubscribers("before", func_get_args());
		}

		function afterAction() {
			$this->setActionSubscribers("after", func_get_args());
		}

		function execute($action, $request) {

			$this->request = $request;

			$this->executeActionSubscribers("before", $action);
			$response = $this->$action();
			$this->executeActionSubscribers("after", $action);

			$this->render($this->_class."/".$action, $response);

		}

		function render($template, $data = null, $layout = true) {

			if (is_null($template)) {

				$this->renderCustom(MimeTypes::Html, null);
				return;

			}

			$this->_actionRendered = true;

			$view = new Render\View($this->viewPath);

			if ($layout === true) {
				$view->setLayout("shared/base");
			}
			else if ($layout === false) {
				$view->setLayout(null);
			}
			else if (is_string($view)) {
				$view->setLayout($layout);
			}
			else {
				throw new Exception("Possible values for layout are 'true', 'false' or layout path.", 1);
			}

			$view->setView($template);

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
			(Routing\RouteDispatch::getSharedInstance())->softRedirect(404);
		}

		function render_500() {
			(Routing\RouteDispatch::getSharedInstance())->softRedirect(500);
		}

		private function setActionSubscribers($trigger) {

			if (func_num_args() < 3) {
				throw new Exception("Too few arguements supplied to ".$trigger."Action(), 2 expected.", 1);
			}

			$action = func_get_arg(1);

			if (func_num_args() == 3) {
				$subscribers = func_get_arg(2);
			}
			else {
				$subscribers = array_slice(func_get_args(), 2);
			}

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