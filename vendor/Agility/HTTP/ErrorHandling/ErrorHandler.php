<?php

namespace Agility\HTTP\ErrorHandling;

use Agility\HTTP\Routing\Routes;
use Agility\Logging\Logger;

	class ErrorHandler {

		static function attachErrorRoutes() {

			Routes::map(function() {

				foreach ([404, 500] as $statusCode) {
					$this->match($statusCode, "ErrorHandlerController#renderErrorResponse");
				}

			}, "/", "Agility\\HTTP\\ErrorHandling");

		}

		static function catch($errorMsg, $statusCode) {
			Logger::log($errorMsg);
		}

	}

?>