<?php

namespace Agility\Data\Query;

	class Query {

		// Raw query. rawQuery takes precedence over others
		public $rawQuery;

		// Table name
		public $from;
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

			$this->rawQuery = null;

			$this->attributes = [];
			$this->where = null;
			$this->limit = [];
			$this->sequence = [];
			$this->objectOf = "Collection";

		}

		function setRawQuery(RawQuery $query) {
			$this->rawQuery = $query;
		}

		function getRawQuery() {
			return $this->rawQuery ?? null;
		}

	}

?>