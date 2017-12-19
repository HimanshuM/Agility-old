<?php

namespace Agility\HTTP\Request;

use Agility\HTTP\Mime\MimeTypes;
use Agility\HTTP\Routing\Routes;
use Agility\HTTP\Routing\Route;

	class Request {

		public $params;
		public $method;
		public $requestUri;

		public $header;

		public $preferredMimeType;
		public $acceptableMimeTypes;

		function __construct($method = "", $requestUri = "", $acceptHeader = "text/html") {

			$this->method = $method;
			$this->requestUri = $requestUri;

			$this->compileAcceptHeader($acceptHeader);

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

			$this->params->get = $_GET ?? [];
			$this->params->post = $_POST ?? [];

			if (in_array($method, ["put", "patch", "delete"])) {
				parse_str(file_get_contents("php://input"), $this->params->{$method});
			}

			foreach ($routeParams as $key => $value) {
				$this->params->get[$key] = $value;
			}

		}

		private function compileAcceptHeader($acceptHeader) {

			if (!empty($acceptHeader)) {

				$acceptableContentTypes = ContentNegotiator::buildAcceptableContentArray($acceptHeader);

				if (count($acceptableContentTypes) == 1) {

					if ($acceptableContentTypes[0] == "*/*") {
						array_unshift($acceptableContentTypes, "text/html");
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

	}

?>