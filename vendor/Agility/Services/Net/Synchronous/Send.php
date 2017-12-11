<?php

namespace Agility\Services\Net\Synchronous;

use Exception;
use Agility\HTTP\Request\Request;

	class Send {

		private $_uri;
		private $_request;

		function __construct(Request $request = null) {
			$this->_request = $request;
		}

		function setRequest(Request $request) {

			if (empty($request)) {
				throw new Exception("Request cannot be empty", 1);
			}

			$this->_request = $request;

		}

		function setRequestUri($uri) {

			if (empty($uri)) {
				throw new Exception("Request URI cannot be empty", 1);
			}

			$this->_uri = $uri;

		}

		function call() {

			if (empty($this->_request) || empty($this->_uri)) {
				throw new Exception("No request specified", 1);
			}

			$get = $this->constructGetData();
			$body = $this->constructRequestBody();

			if (!empty($get)) {
				$this->_uri = $this->_uri."?".$get;
			}

			$curl = $this->prepareCUrl($body);

			$response = \curl_exec($curl);
			\curl_close($curl);

			return $response;

		}

		private function constructGetData() {

			$get = [];
			foreach ($this->_request->params->get as $key => $value) {

				if (is_array($value)) {
					$value = implode(",", $value);
				}
				$get[] = $key."=".urlencode($value);

			}

			return implode("&", $get);

		}

		private function constructRequestBody() {

			$body = [];
			foreach ($this->_request->params->{$this->_request->method} as $key => $value) {

				if (is_array($value)) {
					$value = implode(",", $value);
				}
				$body[] = $key."=".urlencode($value);

			}

			return implode("&", $body);

		}

		private function &prepareCUrl($body) {

			$curl = \curl_init();

			\curl_setopt($curl, CURLOPT_URL, $this->_uri);
			\curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			if ($this->_request->method == "post") {
				\curl_setopt($curl, CURLOPT_POST, true);
			}
			else if (in_array($this->_request->method, "put")) {
				\curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($this->_request->method));
			}

			if ($this->_request->method !== "get") {

				\curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($body)));
				\curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

			}

			return $curl;

		}


	}

?>