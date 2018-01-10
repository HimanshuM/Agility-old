<?php

namespace Agility\Plugin;

use Agility\Configuration\Environment;

	class Plugin {

		public $name;
		public $version;

		protected $environment;

		protected $delayedInvoke;

		function __construct(Environment $environment) {

			$this->environment = $environment;
			$this->delayedInvoke = null;

		}

		function getDelayedInvokeMethod() {
			return $this->delayedInvoke;
		}

		function invokeDelayedMethod() {

			if (!empty($this->delayedInvoke)) {

				if (is_string($this->delayedInvoke)) {
					$this->{$this->delayedInvoke}();
				}
				else if (is_array($this->delayedInvoke)) {

					foreach ($this->delayedInvoke as $method) {
						$this->$method();
					}

				}

			}

		}

	}

?>