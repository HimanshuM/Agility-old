<?php

namespace App\Controllers\Admin;

use Agility\HTTP\Controller;

	class Enrolments extends Controller {

		public $request;

		function edit() {

			var_dump($this->request->params);

		}

	}

?>