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

		const CreatedAt = "created_at";
		const UpdatedAt = "updated_at";

		protected $connectionName;

		private $_dbEngineObj;
		private $_prototype;
		private $_class;

		private $_isDirty;

		function __construct() {

			$this->_dbEngineObj = DatabaseEngine::getSharedInstance();

			$this->initialize();

		}

		static function find($id) {

			$res = new static;

			$res->setAttribute($res->primaryKey, $id);
			$res->refresh();

			return $res;

		}

		static function findMany($ids) {
			return static::findBy((new static)->primaryKey, $ids);
		}

		static function findBy($column, $value) {

			$res = new static;

			$query = $res->newQuery();
			$query->where = new WhereClause($column, $value);

			$results = [];
			$collections = $res->getConnector()->query($query);
			foreach ($collections as $collection) {

				$res = new static;
				$res->fillAttributes($collection);
				$res->_isDirty = false;
				$results[] = $res;

			}

			return $results;

		}

		static function query($query) {

			if (!is_string($query)) {
				return false;
			}

			$results = [];

			$res = new static;

			$query = new Query($query);
			$collections = $res->getConnector()->query($query);
			foreach ($collections as $collection) {

				$res = new static;
				$res->fillAttributes($collection);
				$res->_isDirty = false;
				$results[] = $res;

			}

			return $results;

		}

		function __get($key) {
			return $this->getAttribute($key);
		}

		function __set($key, $value) {
			$this->setAttribute($key, $value);
		}

		function save() {

			$query = $this->newQuery();
			$query->fetch = false;

			$attributes = $this->fetchAttributes();
			if ($this->autoIncrementingPrimaryKey) {
				unset($attributes[$this->primaryKey]);
			}
			if ($this->hasTouchTimestamps) {

				$now = new DateTime();
				$attributes[static::UpdatedAt] = $now->format("Y-m-d H:i:s");
				if (!$this->isDirty()) {
					$attributes[static::CreatedAt] = $now->format("Y-m-d H:i:s");
				}

			}

			$query->attributes = $attributes;

			if ($this->isDirty()) {
				$query->where = new WhereClause($this->primaryKey, $this->getAttribute($this->primaryKey));
			}

			$this->getConnector()->query($query);
			if ($this->autoIncrementingPrimaryKey && !$this->isDirty()) {
				$this->setAttribute($this->primaryKey, $this->getConnector()->getLastInsertId());
			}

		}

		function refresh() {

			$query = $this->newQuery();
			$query->where = new WhereClause($this->primaryKey, $this->getAttribute($this->primaryKey));

			$this->fillAttributes($this->getConnector()->query($query));
			$this->_isDirty = false;

		}

		function sanitize($text) {
			return $this->getConnector()->sanitize($text);
		}

		function isDirty() {
			return $this->_isDirty;
		}

		private function initialize() {

			$this->_class = get_called_class();
			$this->tableName = $this->_class;
			$this->primaryKey = "id";
			$this->autoIncrementingPrimaryKey = true;
			$this->hasTouchTimestamps = true;
			$this->autoTouchTimestampsUpdate = true;

			$this->setDefaultConnectionName();

			$this->_prototype = new Collection($this->_class);
			$this->_isDirty = false;

		}

		private function setDefaultConnectionName() {
			$this->connectionName = $this->_dbEngineObj->getDefaultConnectionIndex();
		}

		protected function getConnector() {
			return $this->_dbEngineObj->getConnectorFromConnectionName($this->connectionName);
		}

		private function fillAttributes(Collection $collection) {

			foreach ($collection->enumerate() as $key => $value) {
				$this->setAttribute($key, $value);
			}

		}

		private function fetchAttributes() {

			$attributes = [];
			foreach ($this->_prototype->enumerate() as $key => $value) {
				$attributes[$key] = $this->getAttribute($key);
			}
			return $attributes;

		}

		private function getAttribute($key) {

			$accessor = "get".$key."Attribute";
			if (method_exists($this, $accessor)) {
				return $this->$accessor($this->_prototype->$key);
			}
			return $this->_prototype->$key;

		}

		private function setAttribute($key, $value) {

			$accessor = "set".$key."Attribute";
			if (method_exists($this, $accessor)) {
				$value = $this->$accessor($value);
			}
			$this->_prototype->$key = $value;

			$this->_isDirty = true;

		}

		private function newQuery() {

			$query = new Query;
			$query->table = $this->table;
			return $query;

		}

	}

?>