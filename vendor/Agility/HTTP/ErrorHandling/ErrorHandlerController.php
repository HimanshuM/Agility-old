<?php

namespace Agility\HTTP\ErrorHandling;

use Agility\HTTP\Controller;
use Agility\HTTP\Mime\MimeTypes;

	class ErrorHandlerController extends Controller {

		function renderErrorResponse() {

			$statusCode = trim($this->request->requestUri, "/");
			$this->{"render".$statusCode}();

		}

		function render_404() {

			$this->response->setStatus(404);
			if ($this->request->preferredContentType == MimeTypes::Html) {
				$this->response->setBody("404 - Not found...");
			}
			$this->response->respond();

		}

		function render_500() {

			$this->response->setStatus(500);

		}

	}

?>