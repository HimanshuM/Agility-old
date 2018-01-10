<?php

namespace Agility\Services\MessageQueue\Client;

use Exception;
use Agility\Services\Net\Asynchronous\UrlWriter;
use Agility\Logging\Logger;
use Agility\HTTP\Request\Request;

	class Client {

		private $_serverDomain;
		private $_listenUri;

		private $_urlWriter;
		private $_callBackUrl;

		function __construct() {
			$this->_urlWriter = new UrlWriter;
		}

		function setServerDomain($serverDomain) {

			if ($serverDomain == "") {
				Logger::log("Message Queue Warning: Listner domain is not specified, which makes open to the internet. It is advisable to use 'localhost' as listner domain");
			}
			$this->_serverDomain = $serverDomain;

		}

		function setListenUri($listenUri) {

			if (empty($listenUri)) {
				throw new Exception("Cannot initialize Message queue client with empty ping URI", 1);
			}

			$this->_listenUri = $listenUri;
		}

		function setCallbackUrl($callback) {
			$this->_callBackUrl = $callback;
		}

		function sendMessage(Request $request) {

			if (empty($request->params->get["job_id"])) {
				throw new Exception("Cannot send message without a job ID", 1);
			}

			$request->method = "POST";

			$request->requestUri = $this->_serverDomain."/".trim($this->_listenUri, "/")."/jobs/".$request->params->get["job_id"];
			unset($request->params->get["job_id"]);
			$request->params->get["cu"] = $this->_callBackUrl;

			$this->_urlWriter->setRequest($request);

			$this->_urlWriter->call();

		}

	}

?>