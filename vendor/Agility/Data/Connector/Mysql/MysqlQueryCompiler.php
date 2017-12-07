<?php

namespace Agility\Data\Connector\Mysql;

	class MysqlQueryCompiler {

		private $query;
		private $rawQuery;

		function __construct(\Agility\Data\Query\Query $query) {
			$this->query = $query;
		}

		function compileSelect() {

			$sql = "SELECT ";
			$sql .= (empty($this->query->attributes) ? "*" : implode(", ", $this->query->attributes));
			$sql .= " FROM ".$this->query->from;

			list($where, $params) = $this->compileWhereClause();
			$sql .= $where;

			$sql .= $this->compileOrdering();

			$sql .= $this->compileLimit();

			return [$sql, $params];

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

			return ["INSERT INTO $table ($columns) VALUES ($values);", $this->parameterizeValues($this->query->attributes)];

		}

		function parameterizeColumns($columns) {

			$cols = [];
			foreach ($columns as $name => $value) {
				$cols[] = ":".$name;
			}

			return $cols;

		}

		function parameterizeValues($columns) {

			$cols = [];
			foreach ($columns as $key => $value) {
				$cols[":".$key] = $value;
			}

			return $cols;
		}

		private function compileWhereClause() {

			$where;
			$params;
			if (!empty($this->query->where)) {

				if (is_array($this->query->where)) {

					$where = [];
					foreach ($this->query->where as $wheres) {

						if (is_array($wheres->value)) {

							$wheres->operator = "IN";
							$params[":".$where->attribute] = "(".implode(", ", $wheres->value).")";

						}
						else {
							$params[":".$wheres->attribute] = $wheres->value;
						}
						$where[] = $wheres->attribute." ".$wheres->operator." :".$wheres->attribute;

					}
					$where = implode(" AND ", $where);

				}
				else {

					if (is_array($this->query->where->value)) {

						$this->query->where->operator = "IN";
						$params[":".$this->query->where->attribute] = "(".implode(", ", $this->query->where->value).")";

					}
					else {
						$params[":".$this->query->where->attribute] = $this->query->where->value;
					}
					$where = $this->query->where->attribute." ".$this->query->where->operator." :".$this->query->where->attribute;

				}

				$where = " WHERE ".$where;

			}
			else {

				$where = "";
				$params = [];

			}

			return [$where, $params];

		}

		private function compileOrdering() {

			$order;
			if (!empty($this->query->sequence)) {

				if (is_array($this->query->sequence)) {

					$order = [];
					foreach ($this->query->sequence as $ordering) {
						$order[] = $ordering->attribute." ".$ordering->order;
					}
					$order = implode(", ", $order);

				}
				else {
					$order = $this->query->sequence->attribute." ".$this->query->sequence->order;
				}

				$order = "ORDER BY ".$order;

			}
			else {
				$order = "";
			}

			return $order;

		}

		private function compileLimit() {

			$limit;
			if (!empty($this->query->limit)) {

				$limit = "LIMIT ".$this->query->limit[0];
				if (isset($this->query->limit[1])) {
					$limit .= ", ".$this->query->limit[1];
				}

			}
			else {
				$limit = "";
			}

			return $limit;

		}

	}

?>