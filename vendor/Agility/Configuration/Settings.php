<?php

namespace Agility\Configuration;

	class Settings {

		private $data;

		private static $_sharedInstance;

		private function __construct() {

		}

		static function getSharedInstance() {

			if (is_null(self::$_sharedInstance)) {
				self::$_sharedInstance = new self;
			}

			return self::$_sharedInstance;

		}

		function __get($key) {

			if (!isset($this->data[$key])) {
				throw new Exception("$key not found in the application configuration", 1);
			}
			return $this->data[$key];

		}

		function __set($key, $value) {
			$this->data[$key] = $value;
		}

	}

?>