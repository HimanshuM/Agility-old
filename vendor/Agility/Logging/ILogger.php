<?php

namespace Agility\Logging;

	interface ILogger {

		function log($msg, $severity = Severity::Info);

	}

?>