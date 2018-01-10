<?php

namespace Agility\HTTP;

use Exception;
use JsonSerializable;

	class Session implements JsonSerializable {

		private $_initialized;

		private static $_sharedInstance;

		private function __construct($name = false) {
			$this->_initialized = false;
		}

		static function getSharedInstance() {

			if (is_null(self::$_sharedInstance)) {
				self::$_sharedInstance = new self;
			}

			return self::$_sharedInstance;

		}

		function init($options = false) {

			if (empty($options)) {
				session_start();
			}
			else {

				if (is_string($options)) {
					session_name($options);
				}
				else if (is_array($options)) {
					session_start($options);
				}
				else {
					throw new Exception("Invalid argument supplied to Session::init()", 1);
				}

			}

			$this->_initialized = true;

		}

		function initialized() {
			return $this->_initialized;
		}

		function name() {
			return session_name();
		}

		function add($key, $value) {
			$_SESSION[$key] = $value;
		}

		function remove($key) {
			unset($_SESSION[$key]);
		}

		function reset() {
			session_reset();
		}

		function unset() {
			session_unset();
		}

		function __get($key) {

			if (!isset($_SESSION[$key])) {
				throw new Exception("'".$key."' not found in Session", 1);
			}

			return $_SESSION[$key];

		}

		function __set($key, $value) {
			$this->add($key, $value);
		}

		function __isset($key) {
			return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
		}

		function __unset($key) {
			unset($_SESSION[$key]);
		}

		/* Serializable overrides */
		function serialize() {
			return serialize($_SESSION);
		}

		function unserialize($attributes) {
			$_SESSION = unserialize($attributes);
		}

		/* JsonSerializable override */
		function jsonSerialize() {
			return $_SESSION;
		}

		/* var_dump override */
		function __debugInfo() {
			return $_SESSION;
		}

	}

?>