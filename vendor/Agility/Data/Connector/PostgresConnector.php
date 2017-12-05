<?php

namespace Agility\Data\Connector;

	class PostgresConnector extends ConnectorBase {

		function __construct() {

			parent::__construct();
			$this->targetPlatform = "postgres";

		}

	}

?>