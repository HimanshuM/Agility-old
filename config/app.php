<?php

use Agility\Services\MessageQueue\Server\Server;

	Application::configure(function() {

		Server::initializeMessageQueueServer("", "mqs");

	});

?>