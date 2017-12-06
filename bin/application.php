<?php

	class Application extends Agility\Application {

		function __construct() {

			parent::__construct();

			if ($this->initialize() == false) {
				header("HTTP/1.1 500");
			}

			if ($this->environment == "development") {
				// set_error_handler(function() { echo "<pre>"; debug_print_backtrace(); });
			}

		}

		function test() {

			$user = new App\Models\User;
			$user->email = "himanshu@gmail.com";
			$user->name = "Himanshu Malpande";

			$user->save();

		}

	}

?>