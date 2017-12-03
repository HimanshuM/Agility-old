<?php

namespace Agility\Data\Query;

	class Query {

		public $table;
		public $fetch;
		public $attributes;
		public $where;
		public $limit;
		public $sequence = [];
		public $objectOf;

		function __construct() {

			$fetch = true;
			$attributes = [];
			$where = null;
			$limit = 0;
			$sequence = [];
			$this->objectOf = "Collection";

		}

	}

?>