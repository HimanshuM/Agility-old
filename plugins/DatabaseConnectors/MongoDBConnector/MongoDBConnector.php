<?php

namespace Plugins\DatabaseConnectors\MongoDBConnector;

use Plugins\DatabaseConnectors\ConnectorBase;

	class MongoDBConnector extends ConnectorBase {

		function __construct() {

			parent::__construct();
			$this->targetPlatform = "mongo";

		}

		function registerSelf() {
			$this->dbInitializer->registerDatabaseConnector($this);
		}

	}

?>