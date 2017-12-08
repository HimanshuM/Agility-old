<?php

namespace Agility\Routing;

	class Routes {

		private static $_sharedInstance;

		private $_get = [];
		private $_post = [];
		private $_put = [];
		private $_patch = [];
		private $_delete = [];

		private function __construct() {

		}

		static function getSharedInstance() {

			if (is_null(self::$_sharedInstance)) {
				self::$_sharedInstance = new Routes;
			}
			return self::$_sharedInstance;

		}

		static function map($callback) {
			($callback->bindTo(self::$_sharedInstance))();
		}

		/** Sequence of parameters for functions:
		 *	1: path: URI,
		 *	2: controller / handler: Controller#action,
		 *	3: constraints: []; "only", "except", "format",
		 *	4: routeName: Name of the route, used in controllers and views
		 *	5: childPaths: Closure; nested path
		**/

		function routes() {

			if (func_num_args() < 2) {
				throw new Exception("Too few arguements supplied to Routes::routes()", 1);
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

		function constructPath($method) {

			if (func_num_args() < 3) {
				throw new Exception("Too few arguments supplied to Routes::".$method."()", 1);
			}

		}

		private function getParametersForRoute($params) {

			$path = $params[0];
			$parameters = array_slice($params, 1);

			$handler = "";
			$constraints = [];
			$pathName = "";
			$childPaths = null;

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
				else if ($param instanceof Closure) {
					$childPaths = $param;
				}

			}

			if ($handler == "") {
				$handler = $path;
			}

			return [$path, $handler, $constraints, $pathName, $childPaths];

		}

	}

?>