<?php

namespace Agility\Data\Connector\Mysql;

	class MysqlQueryCompiler {

		private $query;
		private $rawQuery;

		function __construct(\Agility\Data\Query\Query $query) {
			$this->query = $query;
		}

		function compileSelect() {

		}

		function compileEdit() {

			if (!empty($this->query->where)) {
				$this->compileUpdate();
			}
			else {
				$this->compileInsert();
			}

		}

		function compileInsert() {

			$table = $this->query->from;
			$columns = implode(", ", array_keys($this->query->attributes));

			$values = implode(", ", $this->parameterizeColumns($this->query->attributes));

			return ["INSERT INTO $table ($columns) VALUES ($values);", $query->params];

		}

		function parameterizeColumns($columns) {

			$cols = [];
			$colNames = array_keys($columns);
			foreach ($colNames as $name) {
				$cols[] = ":".$name;
			}

			return $cols;

		}

	}

?>