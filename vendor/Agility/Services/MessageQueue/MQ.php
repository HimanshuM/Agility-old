<?php

namespace Agility\Services\MessageQueue;

use Agility\Logging\Logger;

	class MQ {

		private $_serverDomain;
		private $_listenUri;

		private $_clientObject = [];
		private $_serverObject;

		private static $_sharedInstance;

		private function __construct($serverDomain, $listenUri) {

			$this->_serverDomain = $serverDomain;
			$this->_listenUri = $listenUri;

		}

		static function initialize($serverDomain = "", $listenUri = "") {

			if (is_null(MQ::$_sharedInstance)) {

				MQ::$_sharedInstance = new MQ($serverDomain, $listenUri);
				MQ::$_sharedInstance->initializeMQServer($serverDomain, $listenUri);

			}

			return MQ::$_sharedInstance;
		}

		function &initializeNewMQClient() {

			$clientObject = new Client\Client;

			$clientObject->setServerDomain($this->_serverDomain);
			$clientObject->setListenUri($this->_listenUri);

			$this->_clientObject[] = &$clientObject;

			return $clientObject;

		}

		function &initializeMQServer() {

			if (empty($this->_serverObject)) {
				$this->_serverObject = Server\Server::initializeMessageQueueServer($this->_serverDomain, $this->_listenUri);
			}

			return $this->_serverObject;

		}

	}

?>