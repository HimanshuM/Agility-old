<?php

namespace Plugins\Logger;

use \Agility\Plugin\Plugin;
use \Agility\Logging;
use \Agility\Logging\Severity;

	class Logger extends Plugin implements Logging\ILogger {

		function __construct($environment) {

			parent::__construct($environment);

			Logging\Logger::registerLogger($this);

		}

		function log($msg, $severity = Severity::Info) {

			if ($this->environment == "development" || $this->environment == "testing") {

				if ($severity == Severity::Critical) {
					echo "<h4 style='color: red'>".$msg."</h4>";
				}
				else {
					echo $msg;
				}

			}
			else {
				error_log($severity.": ".$msg);
			}

		}

	}

?>