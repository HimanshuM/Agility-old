<?php

namespace App\Models;

use Agility\Data\Model;

	class User extends Model {

		function __construct() {

			parent::__construct();

			$this->connectionName = "mysql1";
			$this->autoIncrementingPrimaryKey = "false";

		}

		static function deepSearch($text) {

			$query = User::query("SELECT * FROM user WHERE email LIKE ? AND name LIKE ?;", ["%".$text."%", "%".$text."%"]);

		}

	}

?>