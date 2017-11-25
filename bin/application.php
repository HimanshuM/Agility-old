<?php

	class Application extends Agility\Application {

		function __construct() {

			parent::__construct();

			if ($this->initialize() == false) {
				header("HTTP/1.1 500");
			}

		}

	}

?>