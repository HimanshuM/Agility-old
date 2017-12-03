<?php

namespace Agility\Data;

	interface IDatabaseConnector {

		public $targetPlatform;

		function initiateConnection($connectionConfig);

		function query($queryString);
		function exec($execString);

		function insert($model, $attributes);
		function update($model, $attributes, $identifier);
		function delete($model, $identifier);

	}

?>