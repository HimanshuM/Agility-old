<?php

namespace Agility\HTTP\Request;

use Agility\Application;
use Agility\HTTP\Controller;
use Agility\HTTP\ErrorHandling\ErrorHandler;

	class RequestDispatch {

		private $_acceptHeader;

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

			$args = $_SERVER["argv"];
			array_unshift($args);
			$method = "GET";

			$this->_acceptHeader = "text/html";

			$this->resolveRequest($args, $method, null);

		}

		private function parseHttpRequest() {

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

			$request->loadRequestParameters($method, $route->params);

			$this->invokeRequestHandler($route, $request);

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