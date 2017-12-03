<?php

namespace Agility\Data\Query;

	class Ordering {

		public $attribute;
		public $order; /* 0 => ASC, 1 => DESC */

		function __construct($attribute, $order = 0) {

			$this->attribute = $attribute;
			$this->order = $order;

		}

	}

?>