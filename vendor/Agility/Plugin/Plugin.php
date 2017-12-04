<?php

namespace Agility\Plugin;

	class Plugin {

		public $name;
		public $version;

		protected $environment;

		function __construct($environment) {
			$this->environment = $environment;
		}

	}

?>