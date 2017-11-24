<?php

namespace Agility\Logging;

	class Logger {

		public static $loggers = [];

		static function registerLogger(ILogger $logger) {
			self::$loggers[] = $logger;
		}

		static function log($msg, $severity = Severity::Log) {

			if (empty(self::$loggers)) {

				echo $msg;
				return;

			}

			foreach (self::$loggers as $logger) {
				$logger->log($msg, $severity);
			}

		}

	}

?>