<?php

namespace Plugins\DatabaseConnectors\PostgresConnector;

use Plugins\DatabaseConnectors\ConnectorBase;

	class PostgresConnector extends ConnectorBase {

		function __construct() {

			parent::__construct();
			$this->targetPlatform = "postgres";

		}

		function registerSelf() {
			$this->dbInitializer->registerDatabaseConnector($this);
		}

	}

?>