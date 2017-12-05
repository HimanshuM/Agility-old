<?php

namespace Agility\Data\Query;

	class RawQuery {

		public $queryString;
		public $params;

		function __construct($queryString, $params = []) {

			$this->queryString = $queryString;
			$this->params = $params;

		}

	}

?>