<?php

namespace Agility\HTTP\ErrorHandling;

use Agility\HTTP\Controller;
use Agility\HTTP\Mime\MimeTypes;

	class ErrorHandlerController extends Controller {

		function renderErrorResponse() {

			$this->viewPath = $this->applicationDirectory."/public";

			$statusCode = trim($this->request->requestUri, "/");
			$this->{"render".$statusCode}();

			$this->render(null);

		}

		function render_404() {

			if ($this->request->preferredMimeType == MimeTypes::Html) {

				if (file_exists($this->viewPath."/404.html")) {

					ob_start();
					require_once $this->viewPath."/404.html";
					$contents = ob_get_contents();
					ob_end_clean();

					$this->response->setBody($contents);

				}
				else {
					$this->response->setBody("404 - Not found...");
				}

			}
			else {
				$this->response->setStatus(404);
			}

			$this->response->respond();
			$this->render(null);

		}

		function render_500() {

			if ($this->request->preferredMimeType == MimeTypes::Html) {

				if (file_exists($this->viewPath."/500.html")) {

					ob_start();
					require_once $this->viewPath."/500.html";
					$contents = ob_get_contents();
					ob_end_clean();

					$this->response->setBody($contents);

				}
				else {
					$this->response->setStatus(500);
				}

			}
			else {
				$this->response->setStatus(500);
			}

			$this->response->respond();
			$this->render(null);

		}

	}

?>