<?php

namespace Agility\HTTP;

use Agility\HTTP\Response\Response;

	class Controller {

		protected $request;
		protected $response;

		public static $controllersDir;

		private $_beforeSubscribers = [];
		private $_afterSubscribers = [];

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

		function beforeAction() {
			$this->setActionSubscribers("before", func_get_args());
		}

		function afterAction() {
			$this->setActionSubscribers("after", func_get_args());
		}

		function execute($action, $request) {

			$this->request = $request;

			$this->executeActionSubscribers("before", $action);
			$this->$action();
			$this->executeActionSubscribers("after", $action);

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

	}

?>