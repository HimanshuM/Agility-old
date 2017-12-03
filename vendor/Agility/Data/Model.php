<?php

namespace Agility\Data;

use Query\Query;
use Query\WhereClause;
use Query\Ordering;

	class Model {

		protected $tableName;
		protected $primaryKey;
		protected $autoIncrementingPrimaryKey;
		protected $hasTouchTimestamps;
		protected $autoTouchTimestampsUpdate;

		protected $connectionName;

		private $_dbEngineObj;
		private $_prototype;
		private $_class;

		function __construct() {

			$this->_dbEngineObj = DatabaseEngine::getSharedInstance();

			$this->initialize();

		}

		static function find($id) {

			$res = new static;
			return $res->_dbEngineObj->getConnectorFromConnectionName($res->connectionName)->query(new Query(true, [], new WhereClause(new Attribute($res->primaryKey)), $res->_class));

		}

		function __get($key) {

			if (isset($this->_attributes[$key])) {
				return $this->_attributes[$key];
			}
			else {
				throw new Exception("Undefined attribute $key on ".$this->_class, 1);
			}

		}

		function __set($key, $value) {
			$this->_attributes[$key] = $value;
		}

		function save() {



		}

		private function initialize() {

			$this->_class = get_called_class();
			$this->tableName = $this->_class;
			$this->primaryKey = "id";
			$this->autoIncrementingPrimaryKey = true;
			$this->hasTouchTimestamps = true;
			$this->autoTouchTimestampsUpdate = true;

			$this->setDefaultConnectionName();

		}

		private function setDefaultConnectionName() {
			// The default connection obj will be indexed '0';
			$this->connectionName = $this->_dbEngineObj->getDefaultConnectionIndex();
		}

		private function setAttribute($key, $value) {



		}

	}

?>