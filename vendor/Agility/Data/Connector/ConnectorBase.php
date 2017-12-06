<?php

namespace Agility\Data\Connector;

use Agility\Data\IDatabaseConnector;

use PDO;

	abstract class ConnectorBase implements IDatabaseConnector {

		function __construct() {

		}

		abstract function createQuery();

		protected function getPdoConnection($dsn, $username, $password, $config = []) {
			return new PDO($dsn, $username, $password, $config);
		}

	}

?>