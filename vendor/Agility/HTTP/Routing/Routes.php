<?php

namespace Agility\HTTP\Routing;

	class Routes {

		private static $_sharedInstance;

		private $routes = [
			"get" => [],
			"post" => [],
			"put" => [],
			"patch" => [],
			"delete" => []
		];
		private $__namedRoutes;

		private function __construct() {

		}

		static function getSharedInstance() {

			if (is_null(self::$_sharedInstance)) {
				self::$_sharedInstance = new Routes;
			}
			return self::$_sharedInstance;

		}

		static function map($callback, $baseResource = "", $baseNamespace = "") {

			$routeBuilder = new RouteBuilder($baseResource, $baseNamespace);
			($callback->bindTo($routeBuilder))();

			$obj = self::getSharedInstance();
			foreach ($routeBuilder->routes as $route) {
				$obj->prepareRoute($route);
			}

		}

		function getAllRoutes() {
			return $this->routes;
		}

		function getRequestHandler($uri, $method) {

			$route;
			if ($uri == "/") {
				$route = $this->routes[$method]["/"]["0"][0];
			}
			else {

				$tree = &$this->routes[$method];
				$params = [];
				$routeNotFound = false;

				$uriFragments = explode("/", trim($uri, "/ \t\r\n"));
				array_unshift($uriFragments, "/");
				foreach ($uriFragments as $fragment) {

					if (isset($tree[$fragment])) {
						$tree = $tree[$fragment];
					}
					else if(isset($tree[":param"])) {

						$tree = $tree[":param"];
						$params[] = $fragment;

					}
					else {
						return false;
					}

				}

				if (isset($tree[0]->constraints["domain"]) && ($tree[0]->constraints["domain"] !== $_SERVER["HTTP_HOST"])) {
					return false;
				}

				$route = $this->bindParamsFromUri($tree[0], $params);

			}

			return $route;

		}

		// Constructs the routes tree for traversing while serving a request
		private function prepareRoute($route) {

			$urlFragments = explode("/", trim($route->path, "/"));
			if ($urlFragments[0] === "") {
				$urlFragments[0] = "0";
			}
			array_unshift($urlFragments, "/");
			$this->routes[$route->method] = TreeBuilder::buildTree($this->routes[$route->method], $urlFragments, count($urlFragments), 0, $route);

			$this->__namedRoutes[$route->pathName] = $route;

		}

		private function bindParamsFromUri($route, $params) {

			$binding = [];
			for ($i=0; $i < count($params); $i++) {
				$binding[$route->params[$i]] = $params[$i];
			}

			$route->params = $binding;
			return $route;

		}

	}

?>