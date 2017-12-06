<?php

namespace Agility\Data\Connector;

	class ConnectionFactory {

		static function createConnector($dbConfig) {

			$adapter = $dbConfig["adapter"];

			if ($adapter == "mysql") {
				return new Mysql\MysqlConnector;
			}
			else if ($adapter == "postgres") {
				return new Postgres\PostgresConnector;
			}
			else if ($adapter == "mongodb" || $adapter == "mongo") {
				return new MongoDB\MongoDBConnector;
			}
			else {
				return null;
			}

		}

	}