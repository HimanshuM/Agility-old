<?php

namespace Agility\Data;

	interface IDatabaseConnector {

		public $targetPlatform;

		function initiateConnection($connectionConfig);

		function query($queryString);
		function exec($execString);

	}

?>