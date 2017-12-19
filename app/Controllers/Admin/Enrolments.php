<?php

namespace App\Controllers\Admin;

use Agility\HTTP\Controller;

	class Enrolments extends Controller {

		function new() {

		}

		function create() {

			var_dump($_POST);
			$this->render(null);

		}

	}

?>