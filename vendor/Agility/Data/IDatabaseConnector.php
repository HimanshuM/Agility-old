<?php

namespace Agility\Data;

	interface IDatabaseConnector {

		function connect($connectionConfig);

		function query(Query\Query $query);
		function exec(Query\Query $exec);

	}

?>