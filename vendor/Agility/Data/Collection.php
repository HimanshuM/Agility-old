<?php

namespace Agility\Data;

use Iterator;
use Serializable;
use JsonSerializable;
use Agility\Exception\PropertyNotFoundException;

	class Collection implements Iterator, Serializable, JsonSerializable {

		private $_attributes;
		private $_model;

		// Used for foreach iteration pointer
		private $_pointer;

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

		/* Iterator overrides */
		function rewind() {
			$this->_pointer = 0;
		}

		function valid() {
			return $this->_pointer < count($this->_attributes);
		}

		function key() {
			return array_keys($this->_attributes)[$this->_pointer];
		}

		function current() {
			return $this->_attributes[array_keys($this->_attributes)[$this->_pointer]];
		}

		function next() {
			$this->_pointer++;
		}

		/* Serializable overrides */
		function serialize() {
			return serialize($this->_attributes);
		}

		function unserialize($attributes) {
			$this->_attributes = unserialize($attributes);
		}

		/* JsonSerializable override */
		function jsonSerialize() {
			return $this->_attributes;
		}

		/* var_dump override */
		function __debugInfo() {
			return $this->_attributes;
		}

	}

?>