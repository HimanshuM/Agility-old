<?php

namespace Agility\HTTP\ErrorHandling;

use Agility\HTTP\Controller;
use Agility\HTTP\Mime\MimeTypes;

	class ErrorHandlerController extends Controller {

		function renderErrorResponse() {

			$statusCode = trim($this->request->requestUri, "/");
			$this->{"render".$statusCode}();

			$this->render(null);

		}

		function render_404() {

			$this->response->setStatus(404);
			if ($this->request->preferredMimeType == MimeTypes::Html) {
				$this->response->setBody("404 - Not found...");
			}

		}

		function render_500() {

			$this->response->setStatus(500);

		}

	}

?>