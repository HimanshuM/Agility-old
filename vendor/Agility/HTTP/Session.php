<?php

namespace Agility\HTTP;

	class Session {

		function __construct($name = false) {

			if (!empty($name) && !is_string($name)) {
				throw new Exception("Invalid argument supplied to Session::constructor", 1);
			}

			session_name($name);

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
				throw new Exception($key." not found in Session", 1);
			}

			return $_SESSION[$key];

		}

		function __set($key, $value) {
			$this->add($key, $value);
		}

	}

?>