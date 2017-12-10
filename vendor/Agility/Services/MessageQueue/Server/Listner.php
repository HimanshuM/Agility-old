<?php

namespace Agility\Services\MessageQueue\Server;

use Agility\HTTP\Controller;

	class Listner extends Controller {

		function index() {
			var_dump($this->request->params);
		}

	}

?>