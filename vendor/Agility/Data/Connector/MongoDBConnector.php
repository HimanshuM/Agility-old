<?php

namespace Agility\Data\Connector;

	class MongoDBConnector extends ConnectorBase {

		function __construct() {

			parent::__construct();
			$this->targetPlatform = "mongo";

		}

	}

?>