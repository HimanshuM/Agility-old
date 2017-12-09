<?php

namespace Agility\HTTP;

use Agility\Response\Response;

	class Controller {

		protected $request;
		protected $response;

		public static $controllersDir;

		function __construct() {
			$response = new Response;
		}

		static function instantiateController($controller) {

			if (!self::controllerExists($controller)) {
				return false;
			}

			require_once self::getControllerPath($controller);
			$controller = "app\\controllers\\".$controller;
			return new $controller;

		}

		private static function controllerExists($controller) {
			return file_exists(self::getControllerPath($controller));
		}

		private static function getControllerPath($controller) {
			return self::$controllersDir."/".str_replace("\\", "/", $controller).".php";
		}

		function execute($method, $request) {

			$this->request = $request;
			$this->$method();

		}

	}

?>