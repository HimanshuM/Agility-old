<?php

namespace App\Models;

use Agility\Data\Model;

	class User extends Model {

		function __construct() {

			parent::__construct();

			$this->connectionName = "mysql1";

		}

		static function deepSearch($text) {

			$query = User::sanitizeQuery("SELECT * FROM user WHERE email LIKE #1 AND name LIKE #1;", "%".$email"%");

		}

	}

?>