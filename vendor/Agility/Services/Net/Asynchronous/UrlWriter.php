<?php

namespace Agility\Services\Net\Asynchronous;

use Agility\HTTP\Request\Request;
use Agility\Logging\Logger;

	class UrlWriter {

		public $errorNumber;
		public $errorString;

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

		function call() {

			if (empty($this->_request)) {
				throw new Exception("Request cannot be empty", 1);
			}

			$get = $this->constructGetData();
			$post = $this->constructPostData();

			list($host, $port, $path) = $this->getSocketParameters();

			$url = $path."?".$get;

			$errno; $errstr;
			try {

				if (($socket = fsockopen($host, $port, $errno, $errstr)) === false) {

					$this->errorNumber = $errno;
					$this->errorString = $errstr;

					Logger::log("Socket open error: Failed to connect to socket. ".$errstr." (".$errno.")");
					return false;

				}

			}
			catch (Exception $e) {

				$this->errorString = $e->getMessage();
				$this->errorNumber = 0;

				Logger::log("Socket open error: Failed to connect to socket. ".$this->errorString);
				return false;

			}

			$data = strtoupper($this->_request->method)." ".$url." HTTP/1.1\r\n";
			$data .= "Host: ".$host."\r\n";
			$data .= "Content-type: application/x-www-form-urlencode\r\n";
			$data .= "Content-length: ".strlen($post)."\r\n";
			$data .= "Connection: Close\r\n\r\n";
			if (!empty($post)) {
				$data .= $post;
			}

			fwrite($socket, $data);
			fclose($socket);

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

		private function constructPostData() {

			$post = [];
			foreach ($this->_request->params->post as $key => $value) {

				if (is_array($value)) {
					$value = implode(",", $value);
				}
				$post[] = $key."=".urlencode($value);

			}
			return implode("&", $post);

		}

		private function getSocketParameters() {

			$parts = parse_url($this->_request->requestUri);
			if (empty($parts["host"])) {
				$parts = parse_url("//".$this->_request->requestUri);
			}

			if (empty($parts["port"])) {

				if (!empty($parts["scheme"])) {

					if ($parts["scheme"] == "http") {
						$parts["port"] = "80";
					}
					else if ($parts["scheme"] == "https") {
						$parts["port"] = "443";
					}

				}
				else {
					$parts["port"] = "80";
				}

			}

			return [$parts["host"], $parts["port"], $parts["path"]];

		}

	}

?>