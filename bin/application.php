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
			$user->email = "himanshu@jigsawacademy.com";
			$user->shortName = "Himanshu Malpande";

			// $user->save();
			echo "<pre>";
			echo json_encode(App\Models\User::findBy("status", 1), JSON_PRETTY_PRINT);

			echo Agility\Extensions\String\Str::camelCase("user_login_method");

		}

	}

?>