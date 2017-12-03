<?php

namespace Agility\Data\Query;

	class WhereClause {

		public $attribute;
		public $operator;
		public $value;

		function __construct($attribute, $value, $operator = "=") {

			$this->attribute = $attribute;
			$this->operator = $operator;
			$this->value = $value;

		}

	}

?>