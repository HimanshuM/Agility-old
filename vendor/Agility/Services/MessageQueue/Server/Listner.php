<?php

namespace Agility\Services\MessageQueue\Server;

use Agility\HTTP\Controller;
use Agility\Logging\Logger;
use Agility\HTTP\Request\Request;
use Agility\Services\Net\Synchronous\Send;

	class Listner extends Controller {

		private $_jobId;
		private $_callBack;
		private $_requestType;
		private $_payload;

		private $_request;

		function index() {

			$this->logRequest();

			$this->_jobId = $this->request->params->get["job_id"];
			$this->_callback = urldecode($this->request->params->get["cu"] ?? "");
			$this->_requestType = $this->request->params->post["type"] ?? "";
			$this->_payload = $this->request->params->post["payload"] ?? $this->request->params->post;

			$requestHandlers = $this->getHandlers();

			list($status, $response, $error) = $this->executeHandlers($requestHandlers);

			$this->prepareResponse($status, $response, $error);

			$this->sendResponse();

		}

		private function logRequest() {
			Logger::log("Received request for job ID ".$this->request->params->get["job_id"]);
		}

		private function getHandlers() {
			return (Server::initializeMessageQueueServer())->getHandlers($this->_requestType);
		}

		private function executeHandlers($requestHandlers) {

			$status = "success";
			$responses = [];
			$errors = [];
			foreach ($requestHandlers as $handler) {

				try {
					list($response, $propagate) = $handler->handle($jobId, $this->_requestType, $payload);
				}
				catch (Exception $e) {

					$errors[] = $e->getMessage();
					$status = "handler_error";
					continue;

				}

				$responses[] = $response;
				if ($propagate == false) {
					break;
				}

			}

			if (empty($handlers)) {

				$errors[] = "No handlers were registered".($this->_requestType != "" ? " for request type ".$this->_requestType : "");
				$status = "no_handlers";

			}

			return [$status, $responses, $errors];

		}

		private function prepareResponse($status, $response, $error) {

			$this->_request = new Request("post");

			$this->_request->params->get = ["job_id" => $this->_jobId, "status" => $status];
			$this->_request->params->post = ["response" => $response, "errors" => $error];

		}

		private function sendResponse() {

			$sender = new Send($this->_request);
			$sender->setRequestUri($this->_callback);

			$sender->call();

		}

	}

?>