<?php

namespace Agility\Services\MessageQueue;

	interface IMQRequestHandler {

		function handle($jobId, $payload);

	}

?>