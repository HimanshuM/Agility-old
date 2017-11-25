<?php

namespace Agility\Plugin;

	class Plugin {

		public $name;
		public $version;

		protected $environment;

		protected $db;

		function __construct($environment) {
			$this->environment = $environment;
		}

	}

?>