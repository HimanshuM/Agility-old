<?php

use Agility\Services\MessageQueue\MQ;

	Application::configure(function() {

		MQ::initialize("agility.com", "mqs");

	});

?>