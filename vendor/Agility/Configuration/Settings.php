<?php

namespace Agility\Configuration;

	class Settings {

		private $data;

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