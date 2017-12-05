<?php

namespace Agility\Data\Connector\Mysql;

use Agility\Data\Connector\ConnectorBase;
use Agility\Logging\Logger;

	class MysqlConnector extends ConnectorBase {

		public $connection;

		function __construct() {

			parent::__construct();

		}

		function connect($config) {

			$host = $this->getHost($config);
			$port = $this->getPort($config);
			$unixSocket = $this->getUnixSocket($config);

			$dbName = $this->getDBName($config);
			$username = $this->getUsername($config);
			$password = $this->getPassword($config);

			$config = $this->getExtraConfig($config);

			$this->connection = $this->getPdoConnection($this->getDsn(), $username, $password, $config);

		}

		function createQuery() {
			return new MysqlQuery;
		}

		function query(\Agility\Data\Query\Query $query) {

			if (!empty($query->rawQuery) && !empty($query->rawQuery->query)) {

				$query = $query->rawQuery;
				$params = $query->rawQuery->params ?? null;

			}
			else {
				list($query, $params) = (new MysqlQueryCompiler($query))->compileSelect();
			}

			return $this->runQuery($query, $params);

		}

		function exec(\Agility\Data\Query\Query $query) {

		}

		function insert(\Agility\Data\Query\Query $query) {

			list($query, $params) = (new MysqlQueryCompiler($query))->compileInsert();
			return $this->runQuery($query, $params);

		}

		function insertAndGetId(\Agility\Data\Query\Query $query) {

		}

		function update(\Agility\Data\Query\Query $query) {

		}

		private function getHost($connectionConfig) {

			if (!isset($connectionConfig["host"])) {

				Logger::log("Hostname not specified for database connection. Will try for Unix socket, if not found, localhost (127.0.0.1) will be used.");
				return "127.0.0.1";

			}
			return $connectionConfig["host"];

		}

		private function getPort($config) {
			return $config["port"] ?? null;
		}

		private function getUnixSocket($config) {
			return $config["unix_socket"] ?? null;
		}

		private function getDBName($config) {
			return $this->getConfiguration($config, "database", "Database name not specified.");
		}

		private function getUsername($config) {
			return $this->getConfiguration($config, "username", "Username not specified.");
		}

		private function getPassword($config) {
			return $this->getConfiguration($config, "password", "Password not specified. Using empty password", false);
		}

		private function getExtraConfig($config) {

			$configuration = [];
			if (isset($config["config"])) {

				if (!empty($config["config"]["persistent"]) || intval($config["config"]["persistent"]) != 0) {
					$configuration[PDO::ATTR_PERSISTENT] = true;
				}

			}

			$configuration[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

			return $configuration;

		}

		// If both hostname and Unix socket are specified, precedence will be given to the Unix socket
		private function getDsn($db, $host = null, $port = null, $unixSocket = null, $charSet = null) {

			if (empty($unixSocket) && empty($host)) {
				throw new Exception("Cannot connect to Mysql database, neither host nor unix socket is specified.", 1);
			}

			return "mysql:dbname=".$db.(!empty($unixSocket) ? ";unix_socket=".$unixSocket : "").(empty($unixSocket) && !empty($host) ? ";host=".$host.(!empty($port) ? ";port=".$port : "") : "").(!empty($charSet) ? ";charset=".$charSet);

		}

		private function getConfiguration($config, $key, $errorString, $exception = true) {

			if (!isset($config[$key])) {

				if ($exception) {
					throw new Exception($errorString, 1);
				}
				else {
					Logger::log($errorString);
				}

			}
			return $config[$key];

		}

		private function compileQuery($query) {

		}

		private function runQuery($query, $params = null) {

			$stmt = $this->connection->prepare($query);
			if (!empty($params)) {

				if (is_array($params[0])) {

					$results = [];

					foreach ($params as $set) {

						$res = $stmt->execute($set);
						$results[] = $res->fetchAll();

					}

					return $results;

				}
				else {
					return $stmt->execute($params);
				}

			}

		}

	}

?>