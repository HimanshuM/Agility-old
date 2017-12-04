<?php

namespace Agility\Data\Query;

	class Query {

		public $connection;

		// Raw query. query takes precedence over others
		public $query;

		// Table name
		public $table;
		// true: SELECT | false: INSERT or UPDATE
		public $fetch;
		// Array of columns, [] = All columns
		public $attributes;
		// Object of class WhereClause
		public $where;
		// [0]: Count; if isset([1]), From | [1]: To
		public $limit;
		// Object of class Ordering
		public $sequence = [];
		// DB connector returns an object of this type
		public $objectOf;

		function __construct() {

			$query = "";

			$fetch = true;
			$attributes = [];
			$where = null;
			$limit = [];
			$sequence = [];
			$this->objectOf = "Collection";

		}

	}

?>