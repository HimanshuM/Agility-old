<?php

namespace Agility\Data;

use DateTime;
use Iterator;
use Serializable;
use JsonSerializable;
use Agility\Data\Query\Ordering;
use Agility\Data\Query\Query;
use Agility\Data\Query\RawQuery;
use Agility\Data\Query\WhereClause;
use Agility\Extensions\String\Str;
use Agility\Extensions\String\Inflect;

	class Model implements Iterator, Serializable, JsonSerializable {

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
		private $_freshObject;

		private $_pointer;

		function __construct() {

			$this->_dbInitObj = Initializer::getSharedInstance();

			$this->setDefaultConnectionName();

			$this->_prototype = new Collection($this->_class);
			$this->_isDirty = false;
			$this->_freshObject = true;

			$this->initialize();

		}

		static function find($id) {

			$res = new static;

			$res->setAttribute($res->primaryKey, $id);
			$res->refresh();

			return $res;

		}

		static function findMany($ids, $ordering = []) {
			return static::findBy((new static())->primaryKey, $ids, $ordering);
		}

		static function findBy($column, $value, $ordering = []) {
			return static::where([$column => $value], $ordering);
		}

		static function where($clause, $ordering = []) {

			$res = new static();

			$query = $res->newQuery();
			$query->where = [];
			foreach ($clause as $attribute => $value) {
				$query->where[] = new WhereClause($res->getStorageName($attribute), $value);
			}

			if (empty($ordering)) {
				$query->sequence[] = new Ordering("id");
			}
			else {

				foreach ($ordering as $sequence) {

					if (!($sequence instanceof Ordering)) {
						throw new Exception("Ordering should be an object of class Ordering", 1);
					}

				}

				$query->sequence = $ordering;

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

		static function all() {

			$res = new static();
			$query = $res->newQuery();

			$query->sequence[] = new Ordering("id");

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

		static function query($queryString, $params = []) {

			if (!is_string($queryString)) {
				return false;
			}

			$res = new static();
			$query = $res->newQuery();
			$query->rawQuery = new RawQuery($queryString);

			if (func_num_args() > 1) {

				if (is_array($params)) {
					$query->rawQuery->params = $params;
				}
				else {
					$query->rawQuery->params = array_slice(func_get_args(), 1);
				}

			}

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

			$attributes = $this->fetchAttributes();
			if ($this->autoIncrementingPrimaryKey) {
				unset($attributes[$this->getStorageName($this->primaryKey)]);
			}
			if ($this->hasTouchTimestamps) {

				if (!$this->autoTouchTimestampsUpdate) {

					$now = new DateTime();
					$attributes[static::UpdatedAt] = $now->format("Y-m-d H:i:s");
					if (!$this->isDirty()) {
						$attributes[static::CreatedAt] = $now->format("Y-m-d H:i:s");
					}

				}

			}

			$query->attributes = $attributes;

			if ($this->isDirty()) {

				$query->where = new WhereClause($this->getStorageName($this->primaryKey), $this->getAttribute($this->primaryKey));
				$this->getConnector()->update($query);

			}
			else {

				if ($this->autoIncrementingPrimaryKey) {

					$key = $this->getConnector()->insertAndGetId($query);
					$this->setAttribute($this->primaryKey, $key);

				}
				else {
					$this->getConnector()->insert($query);
				}

				$this->_freshObject = true;

			}

			$this->_isDirty = false;

		}

		function refresh() {

			$query = $this->newQuery();
			$query->where = new WhereClause($this->getStorageName($this->primaryKey), $this->getAttribute($this->primaryKey));

			$this->fillAttributes($this->getConnector()->query($query)[0]);
			$this->_isDirty = false;

		}

		function isDirty() {
			return $this->_isDirty;
		}

		function isFreshObject() {
			return $this->_freshObject;
		}

		/* Iterator overrides */
		function rewind() {
			$this->_pointer = 0;
		}

		function valid() {
			return $this->_pointer < count($this->_prototype->enumerate());
		}

		function key() {
			return array_keys($this->_prototype->enumerate())[$this->_pointer];
		}

		function current() {
			return $this->getAttribute(array_keys($this->_prototype->enumerate())[$this->_pointer]);
		}

		function next() {
			$this->_prototype++;
		}

		/* Serializable overrides */
		function serialize() {
			return $this->_prototype->serialize();
		}

		function unserialize($prototype) {
			$this->_prototype->unserialize();
		}

		/* JsonSerializable override */
		function jsonSerialize() {
			return $this->_prototype->enumerate();
		}

		/* var_dump override */
		function __debugInfo() {
			return $this->_prototype->enumerate();
		}

		private function initialize() {

			$this->_class = get_called_class();
			$this->tableName = $this->getStorageName($this->getTableNameFromQualifiedClassName());
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

			if (!$this->_freshObject) {
				$this->_isDirty = true;
			}

		}

		private function newQuery() {

			$query = $this->getConnector()->createQuery();
			$query->from = $this->getStorageName($this->tableName);
			return $query;

		}

		private function getNormalizedName($attribute) {
			return Str::pascalCase($attribute);
		}

		private function getStorageName($attribute) {
			return Str::snakeCase($attribute);
		}

		private function getTableNameFromQualifiedClassName() {

			$segments = explode("\\", $this->_class);
			return Inflect::pluralize(array_pop($segments));

		}

	}

?>