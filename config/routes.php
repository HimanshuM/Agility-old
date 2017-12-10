<?php

	Agility\HTTP\Routing\Routes::map(function() {

		$this->prefix("accounts", function() {
			$this->resources("courses");
		});

		$this->resources("users");
		$this->get("/", "home#index");

		$this->resources("students", function() {
			$this->namespace("admin", function() {
				$this->resources("enrolments");
			});
		});

		$this->scoped("admin", function() {
			$this->resources("subscribers");
		});

	});

?>