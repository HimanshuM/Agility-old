<?php

namespace Agility\Extensions;

	abstract class Enum {

		public const __default = 0;

		private $_internal;

		function __construct($value = null) {

			if (!is_null($value)) {
				$this->_internal = $value;
			}
			else {
				$this->_internal = static::__default;
			}

		}

		function __toString() {
			return "".$this->_internal;
		}

		function toString() {
			return "".$this->_internal;
		}

	}

?>