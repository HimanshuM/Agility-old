<?php

namespace Plugins\DatabaseConnectors;

use Agility\Data\IDatabaseConnector;
use Agility\Data\Initializer;

	abstract class ConnectorBase implements IDatabaseConnector {

		public $targetPlatform;

		protected $dbInitializer;

		function __construct() {
			$this->dbInitializer = Initializer::getSharedInstance();
		}

		abstract function registerSelf();

	}

?>