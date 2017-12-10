<?php

namespace Agility\Services\MessageQueue\Client;

use Agility\Services\Net\Asynchronous\UrlWriter;
use Agility\HTTP\Request\Request;

	class Client {

		protected $serverUrl;

		private $_urlWriter;
		private $_callBackUrl;

		function __construct($server = "") {

			$this->serverUrl = $server;

			$this->_urlWriter = new UrlWriter;

		}

		function setServer($server) {
			$this->serverUrl = $server;
		}

		function setCallbackUrl($callback) {
			$this->_callBackUrl = $callback;
		}

		function sendMessage(Request $request) {

			$request->requestUri = $this->serverUrl."/".trim($request->requestUri, "/");
			$request->params->get["callback"] = $this->_callBackUrl;

			$this->_urlWriter->setRequest($request);

			$this->_urlWriter->call();

		}

	}

?>