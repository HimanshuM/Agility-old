<?php

namespace Agility\HTTP\Routing;

use Closure;
use Exception;

	class RouteBuilder {

		public $routes = [];

		private $_baseResource;
		public $_baseNamespace;

		function __construct($baseResource = "", $baseNamespace = "") {

			if ($baseResource == "") {
				$baseResource = "/";
			}
			$this->_baseResource = $baseResource;

			if ($baseNamespace == "") {
				$baseNamespace = "App\\Controllers";
			}
			$this->_baseNamespace = $baseNamespace;

		}

		// Routes with a prefix fragment which does not relate to a namespace
		function prefix($prefix, Closure $callback) {

			$prefix = $this->normalizePath($prefix);
			$builder = new RouteBuilder($prefix);
			($callback->bindTo($builder))();
			$this->routes = array_merge($this->routes, $builder->routes);

		}

		// Routes with prefix fragment which relates to namespace of the handler
		function namespace($namespace, Closure $callback) {

			$prefix = $this->normalizePath($namespace);
			$builder = new RouteBuilder($prefix, $this->_baseNamespace."\\".trim($namespace, "\\"));
			($callback->bindTo($builder))();
			$this->routes = array_merge($this->routes, $builder->routes);

		}

		// Handlers with no prefix but placed inside a namespace
		function scoped($scope, Closure $callback) {

			$builder = new RouteBuilder("/", $this->_baseNamespace."\\".trim($scope, "\\"));
			($callback->bindTo($builder))();
			$this->routes = array_merge($this->routes, $builder->routes);

		}

		/** Sequence of parameters for functions:
		 *	1: path: URI,
		 *	2: controller / handler: Controller#action,
		 *	3: constraints: []; "only", "except", "format",
		 *	4: routeName: Name of the route, used in controllers and views
		 *	5: childResources: Closure; nested path
		**/

		function resources() {

			if (func_num_args() < 1) {
				throw new Exception("Too few arguements supplied to Routes::routes(), at least 1 expected", 1);
			}

			list($resources, $controller, $constraints, $pathName, $childResources) = $this->parseBuildParams(func_get_args());
			$actions = $this->getValidActions($constraints);

			if ($actions["index"] == true) {
				// GET resources/
				$this->get($resources, $controller."#index", $constraints, $resources."_index");
			}
			if ($actions["new"] == true) {
				// GET resources/new
				$this->get($resources."/new", $controller."#new", $constraints, $resources."_new");
			}
			if ($actions["create"] == true) {
				// POST resources/create
				$this->post($resources."/create", $controller."#create", $constraints, $resources."_create");
			}
			if ($actions["show"] == true) {
				// GET resources/:id
				$this->get($resources."/:id", $controller."#show", $constraints, $resources."_show", $childResources);
			}
			if ($actions["edit"] == true) {
				// GET resources/:id/edit
				$this->get($resources."/:id/edit", $controller."#edit", $constraints, $resources."_edit");
			}
			if ($actions["update"] == true) {
				// PATCH resources/:id
				$this->patch($resources."/:id", $controller."#update", $constraints, $resources."_update");
			}
			if ($actions["delete"] == true) {
				// DELETE resources/:id
				$this->delete($resources."/:id", $controller."#delete", $constraints, $resources."_delete");
			}
			if ($actions["save"] == true) {
				// PUT resources/:id
				$this->put($resources."/:id", $controller."#save", $constraints, $resources."_save");
			}

		}

		function get() {
			$this->_get[] = $this->constructPath("get", func_get_args());
		}

		function post() {
			$this->_post[] = $this->constructPath("post", func_get_args());
		}

		function put() {
			$this->_put[] = $this->constructPath("put", func_get_args());
		}

		function patch() {
			$this->_patch[] = $this->constructPath("patch", func_get_args());
		}

		function delete() {
			$this->_delete[] = $this->constructPath("delete", func_get_args());
		}

		private function getValidActions($constraints) {

			$actions = [
				"index" => true,
				"new" => true,
				"create" => true,
				"show" => true,
				"edit" => true,
				"update" => true,
				"delete" => true,
				"save" => true
			];

			if (!empty($constraints["only"])) {

				foreach ($actions as $action) {

					if (is_array($constraints["only"])) {

						if (!in_array($action, $constraints["only"])) {
							$action = false;
						}

					}
					else {

						if ($action != $constraints["only"]) {
							$action = false;
						}

					}

				}

			}
			else if (!empty($constraints["except"])) {

				if (is_array($constraints["except"])) {

					foreach ($constraints["except"] as $action) {
						$actions[$action] = false;
					}

				}
				else {
					$actions[$constraints["except"]] = false;
				}

			}

			return $actions;

		}

		private function normalizePath($prefix) {

			if ($this->_baseResource != "/") {

				$baseResource;
				if ($this->_baseResource instanceof Route) {

					$baseResource = trim($this->getBaseResourceFromRoute($this->_baseResource->path), "/");
					$baseResource = $baseResource."/:".$baseResource."_id";

				}
				else {
					$baseResource = $this->_baseResource;
				}

				$prefix = trim($baseResource, "/")."/".trim($prefix, "/");

			}
			else {
				$prefix = "/".$prefix;
			}

			return $prefix;

		}

		private function getBaseResourceFromRoute($path) {

			if (strrpos($path, ":param") + strlen(":param") == strlen($path)) {
				return substr($path, 0, strrpos($path, ":param"));
			}
			return $path;

		}

		private function prependNamespace($handler) {

			if ($this->_baseNamespace != "") {
				$handler = trim($this->_baseNamespace, "\\")."\\".$handler;
			}
			return $handler;

		}

		private function constructPath($method) {

			if (count(func_get_arg(1)) < 2) {
				throw new \Exception("Too few arguments supplied to Routes::".$method."()", 1);
			}

			list($path, $handler, $constraints, $pathName, $childResources) = $this->parseBuildParams(func_get_arg(1));

			$path = $this->normalizePath($path);
			if ($path == "//") {
				$path = "/";
			}
			$routeParams = $this->getParamsFromPath($path);
			$path = $this->normalizeParameterizedPath($path);

			$handler = $this->prependNamespace($handler);
			if (strpos($handler, "#") === false) {
				throw new \Exception("Controller action not specified for path $path", 1);
			}

			if ($method != "get") {
				$childResources = null;
			}

			$route = $this->constructRoute($method, $path, $handler, $pathName, $constraints, $routeParams);

			$this->routes[] = $route;

			if (!empty($childResources)) {

				$builder = new RouteBuilder($route);
				($childResources->bindTo($builder))();
				$this->routes = array_merge($this->routes, $builder->routes);

			}

		}

		private function parseBuildParams($params) {

			$path = $params[0];
			$parameters = array_slice($params, 1);

			$handler = "";
			$constraints = [];
			$pathName = "";
			$childResources = null;

			$index = 2;

			foreach ($parameters as $param) {

				if (is_string($param)) {

					if ($handler == ""){
						$handler = $param;
					}
					else {
						$pathName = $param;
					}

				}
				else if (is_array($param)) {
					$constraints = $param;
				}
				else if (get_class($param) == "Closure") {
					$childResources = $param;
				}

			}

			if ($handler == "") {
				$handler = $path;
			}

			return [$path, $handler, $constraints, $pathName, $childResources];

		}

		private function getParamsFromPath($path) {

			$matches = [];
			$params = [];
			preg_match_all("/\\/:\\w+/", $path, $matches);
			foreach ($matches[0] as $match) {
				$params[] = substr($match, 1);
			}

			return $params;

		}

		private function normalizeParameterizedPath($path) {
			return preg_replace("/\\/:\\w+/", "/:param", $path);
		}

		private function constructRoute($method, $path, $handler, $pathName, $constraints, $params) {

			$route = new Route;
			$route->method = $method;
			$route->path = $path;
			$route->pathName = $pathName;
			$handler = $this->getControllerAndAction($handler);
			$route->controller = $handler[0];
			$route->action = $handler[1];
			$route->params = $params;
			$route->constraints = $constraints;

			return $route;

		}

		private function getControllerAndAction($handler) {

			$segments = explode("#", $handler);
			$action = array_pop($segments);

			return [implode("\\", $segments), $action];
		}

	}

?>