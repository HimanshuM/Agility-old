<?php

namespace Agility\Services\MessageQueue\Server;

use Agility\HTTP\Routing\Routes;

	class Server {

		private $_hostname;
		private $_listenUrl;

		private $_handlers = [];

		private static $_sharedInstance = null;

		private function __construct($hostname, $listenUrl) {

			if (empty($listenUrl)) {
				throw new \Exception("Cannot initialize Message queue subsystem with empty listener URL.", 1);
			}

			$this->_hostname = $hostname;
			$this->_listenUrl = $listenUrl;

		}

		static function initializeMessageQueueServer($hostname = "", $listenUrl = "") {

			if (is_null(self::$_sharedInstance)) {

				self::$_sharedInstance = new self($hostname, $listenUrl);
				self::$_sharedInstance->addRoute();

			}

			return self::$_sharedInstance;

		}

		function registerHandler(IMQRequstHandler &$handler, $requestType) {
			$this->_handlers[$requestType] = $handler;
		}

		function getHandlers($requestType = "") {

			if ($requestType == "") {
				return $this->_handlers;
			}
			else {
				return $this->_handlers[$requestType] ?? [];
			}

		}

		private function addRoute() {

			Routes::map(function() {

				$temp = Server::initializeMessageQueueServer();
				$constraints = [];
				if (!empty($temp->_hostname)) {
					$constraints["domain"] = $temp->_hostname;
				}

				$this->post($temp->_listenUrl."/jobs/:job_id", "Listner#index", $constraints);

			}, "/", "Agility\\Services\\MessageQueue\\Server");

		}

	}

?>