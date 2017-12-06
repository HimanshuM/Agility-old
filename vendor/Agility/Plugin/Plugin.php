<?php

namespace Agility\Plugin;

use Agility\Environment;

	class Plugin {

		public $name;
		public $version;

		protected $environment;

		function __construct(Environment $environment) {
			$this->environment = $environment;
		}

	}

?>