<?php

namespace Agility\HTTP\Request;

use Agility\Application;
use Agility\HTTP\Controller;
use Agility\HTTP\ErrorHandling\ErrorHandler;

	class RequestDispatch {

		private $_acceptHeader;
		private $_cli;

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

		function softRedirect($location) {

			$_SERVER["REQUEST_URI"] = "/".$location;
			$this->parseHttpRequest();

		}

		private function parseCliRequest() {

			$this->_cli = true;

			$args = $_SERVER["argv"];
			array_unshift($args);
			$method = "GET";

			$_SERVER["REMOTE_ADDR"] = "127.0.0.1";

			$this->_acceptHeader = "text/html";

			$this->resolveRequest($args, $method, null);

		}

		private function parseHttpRequest() {

			$this->_cli = false;

			$method = $_SERVER["REQUEST_METHOD"];
			$uri = $_SERVER["REQUEST_URI"];

			if (strpos($uri, "?") !== false) {
				$uri = explode("?", $uri)[0];
			}

			$this->_acceptHeader = $_SERVER["HTTP_ACCEPT"] ?? "text/html";

			$this->resolveRequest(strtolower($method), $uri);

		}

		private function resolveRequest($method, $uri) {

			$request = new Request($method, $uri, $this->_acceptHeader);

			// echo json_encode((\Agility\HTTP\Routing\Routes::getSharedInstance())->getAllRoutes());

			if (empty($route = (\Agility\HTTP\Routing\Routes::getSharedInstance())->getRequestHandler($uri, $method))) {

				$this->handle(404, $method);
				return;

			}

			if ($this->verifyRouteConstraints($route, $request) === false) {

				$this->handle(500, $method);
				return;

			}

			$request->loadRequestParameters($method, $route->params);

			$this->invokeRequestHandler($route, $request);

		}

		private function verifyRouteConstraints($route, $request) {

			if (!empty($route->constraints["cli_only"]) && $route->constraints["cli_only"] == true) {

				if ($this->_cli != true) {
					return false;
				}

			}

			return $this->verifyIPConstraint($route, $request);

		}

		private function verifyIPConstraint($route, $request) {

			if (!empty($route->constraints["blocked_ip"])) {

				if (is_string($route->constraints["blocked_ip"])) {

					if ($route->constraints["blocked_ip"] == $request->ip) {
						return false;
					}

				}
				else if (is_array($route->constraints["blocked_ip"])) {

					foreach ($route->constraints["blocked_ip"] as $blocked_ip) {

						if ($blocked_ip == $request->ip) {
							return false;
						}

					}

				}

			}

			if (!empty($route->constraints["allowed_ip"])) {

				if (is_string($route->constraints["allowed_ip"])) {

					if ($route->constraints["allowed_ip"] != $request->ip) {
						return false;
					}

				}
				else if (is_array($route->constraints["allowed_ip"])) {

					foreach ($route->constraints["allowed_ip"] as $allowed_ip) {

						if ($allowed_ip == $request->ip) {
							return true;
						}

					}

					return false;

				}

			}

			return true;

		}

		private function invokeRequestHandler($route, $request) {

			try {
				$controller = $this->instantiateController($route->controller);
			}
			catch (Exception $e) {

				ErrorHandler::catch($e);
				$this->softRedirect(500);

			}

			if ($controller === false) {

				$this->handle(500, $method);
				return;

			}

			try {
				$controller->execute($route->action, $request);
			}
			catch (Exception $e) {

				ErrorHandler::catch($e);
				$this->softRedirect(500);

			}

		}

		private function instantiateController($controller) {
			return new $controller;
		}

		private function handle($statusCode, $method) {

			if (empty($route = (\Agility\HTTP\Routing\Routes::getSharedInstance())->getRequestHandler("/_".$statusCode, $method))) {

				$this->sendPrematureResponse(404);
				return;

			}

			$this->invokeRequestHandler($route, (new Request($method, "/_".$statusCode, $this->_acceptHeader)));

		}

		private function sendPrematureResponse($httpCode) {

			$response = new \Agility\HTTP\Response\Response;
			$response->setStatus($httpCode);
			$response->respond();

		}

	}

?>