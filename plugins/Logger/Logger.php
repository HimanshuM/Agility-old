<?php

use \Agility\Logging;
use \Agility\Logging\Severity;

	class Logger implements Logging\ILogger {

		function __construct() {
			Logging\Logger::registerLogger($this);
		}

		function log($msg, $severity = Severity::Log) {

			if ($severity == Severity::Critical) {
				echo "<h4 style='color: red'>".$msg."</h4>";
			}
			else {
				echo $msg;
			}

		}

	}

?>