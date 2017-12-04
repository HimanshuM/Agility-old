<?php

namespace Agility\Data;

use Query\Query;
use Query\WhereClause;
use Query\Ordering;
use Agility\String\String;

	class Model {

		protected $tableName;
		protected $primaryKey;
		protected $autoIncrementingPrimaryKey;
		protected $hasTouchTimestamps;
		protected $autoTouchTimestampsUpdate;

		const CreatedAt = "created_at";
		const UpdatedAt = "updated_at";

		protected $connectionName;

		private $_dbInitObj;
		private $_prototype;
		private $_class;

		private $_isDirty;

		function __construct($empty = false) {

			$this->_dbInitObj = Initializer::getSharedInstance();

			$this->setDefaultConnectionName();

			$this->_prototype = new Collection($this->_class);
			$this->_isDirty = false;

			if (!$empty) {
				$this->initialize();
			}

		}

		static function find($id) {

			$res = new static;

			$res->setAttribute($res->primaryKey, $id);
			$res->refresh();

			return $res;

		}

		static function findMany($ids) {
			return static::findBy((new static(true))->primaryKey, $ids);
		}

		static function findBy($column, $value) {
			return static::where([$column => $value]);
		}

		static function where($clause) {

			$res = new static(true);

			$query = $res->newQuery();
			$query->where = [];
			foreach ($clause as $attribute => $value) {
				$query->where[] = new WhereClause($res->getStorageName($attribute), $value);
			}

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

		static function query($queryString) {

			if (!is_string($queryString)) {
				return false;
			}

			$res = new static(true);
			$query = $res->newQuery();
			$query->query = $queryString;
			return $res->getConnector()->query($query);

		}

		function __get($attribute) {
			return $this->getAttribute($attribute);
		}

		function __set($attribute, $value) {
			$this->setAttribute($attribute, $value);
		}

		function save() {

			$query = $this->newQuery();
			$query->fetch = false;

			$attributes = $this->fetchAttributes();
			if ($this->autoIncrementingPrimaryKey) {
				unset($attributes[$this->getStorageName($this->primaryKey)]);
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
				$query->where = new WhereClause($this->getStorageName($this->primaryKey), $this->getAttribute($this->primaryKey));
			}

			$this->getConnector()->exec($query);
			if ($this->autoIncrementingPrimaryKey && !$this->isDirty()) {
				$this->setAttribute($this->primaryKey, $this->getConnector()->getLastInsertId());
			}

		}

		function refresh() {

			$query = $this->newQuery();
			$query->where = new WhereClause($this->getStorageName($this->primaryKey), $this->getAttribute($this->primaryKey));

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

		}

		private function setDefaultConnectionName() {
			$this->connectionName = $this->_dbInitObj->getDefaultConnectionIndex();
		}

		protected function getConnector() {
			return $this->_dbInitObj->getConnectorFromConnectionName($this->connectionName);
		}

		private function fillAttributes(Collection $collection) {

			foreach ($collection->enumerate() as $attribute => $value) {
				$this->setAttribute($attribute, $value);
			}

		}

		private function fetchAttributes() {

			$attributes = [];
			foreach ($this->_prototype->enumerate() as $attribute => $value) {
				$attributes[$attribute] = $this->getAttribute($attribute);
			}
			return $attributes;

		}

		private function getAttribute($attribute) {

			$accessor = "get".ucfirst($attribute)."Attribute";
			$attribute = $this->getStorageName($attribute);
			if (method_exists($this, $accessor)) {
				return $this->$accessor($this->_prototype->$attribute);
			}
			return $this->_prototype->$attribute;

		}

		private function setAttribute($attribute, $value) {

			$accessor = "set".ucfirst($attribute)."Attribute";
			$attribute = $this->getStorageName($attribute);
			if (method_exists($this, $accessor)) {
				$value = $this->$accessor($value);
			}
			$this->_prototype->$attribute = $value;

			$this->_isDirty = true;

		}

		private function newQuery() {

			$query = new Query;
			$query->connection = $this->connectionName;
			$query->table = $this->getStorageName($this->table);
			return $query;

		}

		private function getNormalizedName($attribute) {
			return String::pascalCase($attribute);
		}

		private function getStorageName($attribute) {
			return String::snakeCase($attribute);
		}

	}

?>