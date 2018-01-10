<?php

namespace Agility\HTTP\Response;

use Exception;

	class Response {

		const Http200 = 200;
		const Http404 = 404;
		const Http500 = 500;

		private $_statusCode;
		private $_mimeType;
		private $_cookies;
		private $_contentLength;
		private $_cacheControl;
		private $_expires;
		private $_cacheMode;
		private $_otherHeaders;
		private $_body;
		private $_downloadFile;

		function __construct() {

			$this->_statusCode = 200;
			$this->_mimeType = "text/html";
			$this->_cookies = [];
			$this->_cacheControl = false;
			$this->_expires = false;
			$this->_cacheMode = "public";
			$this->_otherHeaders = [];
			$this->_body = false;
			$this->_downloadFile = false;

		}

		function setStatus($statusCode) {
			$this->_statusCode = $statusCode;
		}

		function setMimeType($mimeType = "text/html") {
			$this->_mimeType = $mimeType;
		}

		function setCookie($cookie) {

			$args = func_get_args();

			if (count($args) == 1 && is_array($cookie)) {

				if (empty($cookie["name"])) {
					throw new Exception("Cannot set cookie without a name", 1);
				}

				$this->_cookies[] = $cookie;

			}
			else {
				$this->_cookies[] = $cookie;
			}

		}

		function setCacheMode($cacheMode) {

			if (!in_array($cacheMode, ["public", "private_no_expire", "private", "nocache"])) {
				throw new Exception("Invalid cache mode defined. Please use 'public', 'private_no_expire', 'private' or 'nocache'", 1);
			}

			$this->_cacheMode = $cacheMode;

		}

		function disableCaching() {
			$this->_cacheMode = "nocache";
		}

		function addExtraHeader($header) {
			$this->_otherHeaders[] = $header;
		}

		function setBody($body) {
			$this->_body = $body;
		}

		function setDownloadFile($filePath) {
			$this->_downloadFile = $filePath;
		}

		function redirect($location) {

			header("Location: ".$location);
			exit;

		}

		function respond() {

			$this->sendHeaders(($this->_downloadFile !== false));

			if ($this->_downloadFile === false) {

				if ($this->_body !== false && !is_null($this->_body)) {
					echo $this->_body;
				}

			}

		}

		private function sendHeaders($download = false) {

			header("HTTP/1.1 ".$this->_statusCode);

			if ($download) {

				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header("Content-disposition: attachment; filename='".$this->getDownloadFilename()."'");
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');

				header("Content-length: ".filesize($this->_downloadFile));
				readfile($this->_downloadFile);

				exit;

			}
			else {

				header("Content-type: ".$this->_mimeType);

				foreach ($this->_cookies as $cookie) {

					if (!isset($cookie["value"])) {
						$cookie["value"] = "";
					}
					if (!isset($cookie["expire"])) {
						$cookie["expire"] = 0;
					}
					if (!isset($cookie["path"])) {
						$cookie["path"] = "";
					}
					if (!isset($cookie["domain"])) {
						$cookie["domain"] = "";
					}
					if (!isset($cookie["secure"])) {
						$cookie["secure"] = false;
					}
					if (!isset($cookie["httponly"])) {
						$cookie["httponly"] = false;
					}

					setcookie($cookie["name"], $cookie["value"], $cookie["expire"], $cookie["path"], $cookie["domain"], $cookie["secure"], $cookie["httponly"]);

				}

				foreach ($this->_otherHeaders as $key => $value) {
					header($key.": ".$value);
				}

				if ($this->_cacheMode != "public") {
					session_cache_limiter($this->_cacheMode);
				}
				else {

					if ($this->_cacheControl !== false) {
						header("Cache-control: ".$this->_cacheControl);
					}
					if ($this->_expires !== false) {
						header("Expires: ".$this->_expires);
					}

				}

			}

		}

		private function getDownloadFilename() {

			$segments = explode("/", $this->_downloadFile);
			return array_pop($segments);

		}

	}

?>