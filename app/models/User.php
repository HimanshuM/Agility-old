<?php

namespace App\Models;

use Agility\Data\Model;

	class User extends Model {

		function __construct() {

			parent::__construct();

			$this->connectionName = "mysql1";
			// $this->autoIncrementingPrimaryKey = false;

		}

		static function deepSearch($text) {
			var_dump(User::query("SELECT * FROM user WHERE email LIKE ? AND name LIKE ?;", ["%".$text."%", "%".$text."%"]));
		}

		function save() {

			if ($this->isFreshObject()) {
				$this->setUuid();
			}

			parent::save();

		}

		function setUuid() {

			$this->uuid = hash("sha256", $this->email);

		}

	}

?>