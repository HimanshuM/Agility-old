<?php

namespace Agility\HTTP\Request;

use Agility\HTTP\Mime\MimeTypes;
use Agility\HTTP\Routing\Routes;
use Agility\HTTP\Routing\Route;

	class Request {

		public $params;
		public $method;
		public $requestUri;

		public $ip;

		public $header;

		public $preferredMimeType;
		public $acceptableMimeTypes;

		function __construct($method = "", $requestUri = "", $acceptHeader = null, $defaultAccept = "text/html") {

			$this->method = $method;
			$this->requestUri = $requestUri;

			$this->getRequestIP();

			$this->compileAcceptHeader($acceptHeader, $defaultAccept);

			$this->params = new RequestParameters;

			$this->header = getallheaders();

		}

		function getPreferredContentType() {
			return $this->preferredMimeType;
		}

		function getAcceptableContentTypes() {
			return $this->acceptableContentTypes;
		}

		function loadRequestParameters($method, $routeParams = []) {

			if (empty($_GET)) {
				$this->params->get = [];
			}
			else {

				$this->params->get = array_map(function($each) {
					return strip_tags(trim($each));
				}, $_GET);

			}

			$this->params->post = $_POST ?? [];

			if (in_array($method, ["put", "patch", "delete"])) {
				parse_str(file_get_contents("php://input"), $this->params->{$method});
			}

			foreach ($routeParams as $key => $value) {
				$this->params->get[$key] = strip_tags(trim($value));
			}

			if (!empty($_FILES)) {

				foreach ($_FILES as $file) {
					$this->params->files[] = $this->moveUploadedFile($file);
				}
			}

		}

		private function getRequestIP() {

			$headers = ['REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'];
			foreach ($headers as $header) {

				if (!empty($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP) !== false) {
					$this->ip = $_SERVER[$header];
				}

			}

		}

		private function compileAcceptHeader($acceptHeader, $defaultAccept) {

			if (!is_null($acceptHeader)) {

				$acceptableContentTypes = ContentNegotiator::buildAcceptableContentArray($acceptHeader);

				if (count($acceptableContentTypes) == 1) {

					if ($acceptableContentTypes[0] == "*/*") {
						array_unshift($acceptableContentTypes, $defaultAccept);
					}

				}

				foreach ($acceptableContentTypes as $accept) {
					$this->acceptableMimeTypes[] = new MimeTypes($accept);
				}
				$this->preferredMimeType = $this->acceptableMimeTypes[0];

			}
			else {

				$this->acceptableMimeTypes = [];
				$this->preferredMimeType = null;

			}

		}

		private function moveUploadedFile($file) {

			$settings = Settings::getSharedInstance();
			if (!isset($settings->uploadPath)) {
				$settings->uploadPath = "uploads";
			}

			$filename = getcwd()."/".trim($settings->uploadPath, "/")."/".$file["name"];
			move_uploaded_file($file["tmp_name"], $filename);

			return $filename;

		}

	}

?>