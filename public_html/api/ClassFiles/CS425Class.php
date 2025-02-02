<?php

use PgSql\Result;

require_once (dirname(__DIR__) . "/ConfigFiles/Config.php");
require_once (dirname(__DIR__) . "/Exceptions/PGException.php");

class CS425Class
{
	protected PgSql\Connection|false $connect;

	/**
	 * @throws PGException
	 */
	public function __construct(Config|false $config)
	{
		if(!$config){
			$this->connect = false;
		}
		else{
			$this->dbConnect($config);
		}
	}

	public function __destruct(){
		pg_close($this->connect);

		$argc = func_num_args();

		if($argc == 1){
			throw func_get_args()[0];
		}
	}

	/**
	 * @throws PGException
	 */
	private function dbConnect(Config $cfg): void
	{
		// $connection_string = sprintf("host = %s port = %d dbname = %s user = %s password = %s", $cfg->getHost(), $cfg->getPort(), $cfg->getDataBaseName(), $cfg->getUserName(), $cfg->getPassword());
		$this->connect = pg_pconnect($cfg->getConnectionString());
		if(!$this->connect){
			throw new PGException(pg_last_error());
		}
	}

	protected function prepareData($data): string
	{
		return pg_escape_string($this->connect, stripslashes(htmlspecialchars($data)));
	}

	/**
	 * @throws PGException
	 */
	protected function checkQueryResult($result, $errorMessage=""): void
	{
		if(!$result){
			if(strlen($errorMessage) == 0){
				$errorMessage = pg_last_error();
			}
			error_log($errorMessage, 3, "/var/www/html/wcs/public_html/log/php_errors.log");
			throw new PGException($errorMessage);
		}
	}

	/**
	 * @throws PGException
	 */
	public function query($query, string $errorMessage=""): bool|Result
	{
		if(!$this->connect){ return false; }
		$result = pg_query($this->connect, $query);
		$this->checkQueryResult($result, $errorMessage);
		return $result;
	}

	/**
	 * Gets a single result from an SQL query
	 * @param string $query
	 * @return false|string
	 * @throws PGException
	 */
	protected function getBasicResult(string $query): bool|string {
		return pg_fetch_result($this->query($query), 0, 0);
	}
}