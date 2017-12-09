<?php

namespace Agility\Request;

use Agility\Application;

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

			$this->resolveRequest($method, $uri, $acceptHeader);

		}

		private function resolveRequest($method, $uri, $acceptHeader) {

			$request = new Request($method, $uri, $acceptHeader);

			// $route = (Routes::getSharedInstance())->getRequestHandler($uri);

		}

		private function invokeRequestHandler($route) {

			if ($route === false) {
				// Invoke 404 sequence
			}

		}

	}

?>