<?php

namespace App\Controllers;

use Agility\HTTP\Controller;
use Agility\Logging\Logger;

	class Users extends Controller {

		function create() {
			Logger::log(json_encode($this->request->params));
		}

	}

?>