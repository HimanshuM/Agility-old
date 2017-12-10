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

		public $preferredContentType;
		public $acceptableContentTypes;

		function __construct($method, $requestUri, $acceptHeader) {

			$this->method = $method;
			$this->requestUri = $requestUri;

			$this->compileAcceptHeader($acceptHeader);

			$this->header = getallheaders();

		}

		function getPreferredContentType() {
			return $this->preferredContentType;
		}

		function getAcceptableContentTypes() {
			return $this->acceptableContentTypes;
		}

		private function compileAcceptHeader($acceptHeader) {

			if (!empty($acceptHeader)) {

				$acceptableContentTypes = ContentNegotiator::buildAcceptableContentArray($acceptHeader);
				foreach ($acceptableContentTypes as $accept) {
					$this->acceptableContentTypes[] = new MimeTypes($accept);
				}
				$this->preferredContentType = $this->acceptableContentTypes[0];

			}
			else {

				$this->acceptableContentTypes = [];
				$this->preferredContentType = null;

			}

		}

	}

?>