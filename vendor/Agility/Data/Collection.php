<?php

namespace Agility\Data;

	class Collection {

		public $attributes;

		function __construct() {
			$this->attributes = [];
		}

		function setAttribute($key, $value) {

			if (isset($this->attributes[$key])) {

				if (!$this->attributes[$key]->nullable && is_null($value)) {
					throw new Exception("Value of $key cannot be null", 1);
				}

			}

		}

	}

?>