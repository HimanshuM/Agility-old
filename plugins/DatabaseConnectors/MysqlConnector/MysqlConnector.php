<?php

namespace Plugins\DatabaseConnectors\MysqlConnector;

use Plugins\DatabaseConnectors\ConnectorBase;

	class MysqlConnector extends ConnectorBase {

		function __construct() {

			parent::__construct();
			$this->targetPlatform = "mysql";

		}

		function registerSelf() {
			$this->dbInitializer->registerDatabaseConnector($this);
		}

		function connect($connectionConfig) {

		}

		function query(\Agility\Data\Query\Query $query) {

		}

		function exec(\Agility\Data\Query\Query $query) {

		}

	}

?>