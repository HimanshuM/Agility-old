<?php

namespace Agility\HTTP\Request;

use Agility\Application;
use Agility\HTTP\Controller;

	class RequestDispatch {

		private static $_sharedInstance;

		private function __construct() {

		}

		static function getSharedInstance() {

			if (is_null(self::$_sharedInstance)) {
				self::$_sharedInstance = new RequestDispatch();
			}

			return self::$_sharedInstance;

		}

		function processRequest() {

			if ((Application::getApplicationInstance())->isCli()) {
				$this->parseCliRequest();
			}
			else {
				$this->parseHttpRequest();
			}

		}

		private function parseCliRequest() {

			$args = $_SERVER["argv"];
			array_unshift($args);
			$method = "GET";

			$this->resolveRequest($args, $method, null);

		}

		private function parseHttpRequest() {

			$uri = $_SERVER["REQUEST_URI"];
			$method = $_SERVER["REQUEST_METHOD"];

			$acceptHeader = $_SERVER["HTTP_ACCEPT"];

			$this->resolveRequest(strtolower($method), $uri, $acceptHeader);

		}

		private function resolveRequest($method, $uri, $acceptHeader) {

			$request = new Request($method, $uri, $acceptHeader);

			// echo json_encode((\Agility\HTTP\Routing\Routes::getSharedInstance())->getAllRoutes());

			if (($route = (\Agility\HTTP\Routing\Routes::getSharedInstance())->getRequestHandler($uri, $method)) === false) {
				// Invoke 404 sequence
				echo "HTTP/1.1 404";
				return;
			}

			$request->loadRequestParameters($method, $route->params);

			$this->invokeRequestHandler($route, $request);

		}

		private function invokeRequestHandler($route, $request) {

			$controller = $this->instantiateController($route->controller, $request);
			if ($controller === false) {
				echo "HTTP/1.1 500";
				return;
			}

			$controller->execute($route->action, $request);

		}

		private function instantiateController($controller) {
			return new $controller;
		}

	}

?>