<?php

namespace App\Controllers;

use Agility\HTTP\Controller;
use Agility\Logging\Logger;
use Agility\Services\MessageQueue\MQ;
use Agility\HTTP\Request\Request;

	class Home extends Controller {

		function index() {

			$mq = MQ::initialize();
			$client = $mq->initializeNewMQClient();

			$client->setCallbackUrl("agility.com/users/create");

			$request = new Request();
			$request->params->get["job_id"] = 12345;
			$client->sendMessage($request);

		}

	}

?>