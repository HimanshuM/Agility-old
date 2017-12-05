<?php

namespace Agility\Data\Connector;

	class ConnectionFactory {

		static function createConnector($adapter) {

			if ($adapter == "mysql") {
				return new MysqlConnector;
			}
			else if ($adapter == "postgres") {
				return new PostgresConnector;
			}
			else if ($adapter == "mongodb" || $adapter == "mongo") {
				return new MongoDBConnector;
			}
			else {
				return null;
			}

		}

	}