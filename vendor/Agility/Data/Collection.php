<?php

namespace Agility\Data;

use Agility\Exception\PropertyNotFoundException;

	class Collection {

		private $_attributes;
		private $_model;

		function __construct($model = "Model") {

			$this->_attributes = [];
			$this->_model = $model;

		}

		function __get($key) {

			if (isset($this->_attributes[$key])) {
				return $this->_attributes[$key];
			}
			throw new PropertyNotFoundException($this->_model, $key);

		}

		function __set($key, $value) {
			$this->_attributes[$key] = $value;
		}

		function enumerate() {
			return $this->_attributes;
		}

	}

?>