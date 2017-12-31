<?php

namespace Agility\Data\Query;

	class Ordering {

		public $attribute;
		public $ascending;

		function __construct($attribute, $ascending = true) {

			$this->attribute = $attribute;
			$this->ascending = $ascending;

		}

	}

?>