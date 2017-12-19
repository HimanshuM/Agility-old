<?php

	Agility\HTTP\Routing\Routes::map(function() {

		$this->namespace("admin", function() {
			$this->resources("enrolments");
		});

	});

?>