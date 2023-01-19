<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once "CS425Class.php";
require_once(dirname(__DIR__) . "/ConfigFiles/AuthConfig.php");
require_once(dirname(__DIR__) . "/constants.php");

class Authentication extends CS425Class
{
	private string $charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

	public function __construct()
	{
		parent::__construct(new AuthConfig());
	}

	/**
	 * @throws PGException
	 */
	public function setTOTP(string $username, string $key){
		$result = $this->query("UPDATE Logins SET totp_secret = '%s' WHERE username = '%s'", $key, $username);
	}

	/**
	 * @throws PGException
	 */
	public function removeTOTP(string $username){
		$result = $this->query("UPDATE Logins SET totp_secret = NULL WHERE username = '%s'", $username);
	}

	# region Creating TOTP secret key
	public function checkTOTP(string $username, string $authcode, bool $isEmployee) : bool
	{
		$username = $this->prepareData($username);
		$table_name = $isEmployee ? "EmployeeLogins" : "Logins";
		$key = $this->getBasicResult(sprintf("SELECT totp_secret FROM %s WHERE username = '%s'", $table_name, $username));
		$totps = $this->GenerateCloseTokens($key);
		return in_array($authcode, $totps);
	}

	/**
	 * Creates a secret key for TOTP.
	 * @param int $length The number of digits in the secret key
	 * @return string
	 * @throws Exception
	 */
	function createSecretKey(int $length=16): string {
		$token = "";

		for($i = 0; $i < $length; $i++){
			$token .= substr($this->charset, random_int(0, strlen($this->charset)), 1);
		}

		return $token;
	}

	/**
	 * @param $username
	 * @param $key
	 * @param $length
	 * @param $period
	 * @return string
	 */
	public function generateQRUri($username, $key, $length=6, $period=30): string {
		$cmd = sprintf("python3 %s/qr.py %s %s -d=%d -t=%d",
			dirname(__FILE__), $key, $username, $length, $period);
		exec($cmd, $output, $retval);
		return $output["0"];
	}
	# endregion

	# region Generate TOTP
	/**
	 * Generates a Time based One Time Password (TOTP).
	 *
	 * @param string $key The secret key.
	 * @param float|int|null $time The time the code should be generated.
	 * @param int $length The length of the code. Usually 6 or 8.
	 * @param int $time_interval The length in between code generation.
	 * @param string $algo The algorithm being used. Default is sha1.
	 * @return string
	 */
	public function GenerateToken(string $key, float|int|null $time = null, int $length = 6, int $time_interval=30, string $algo="sha1") : string
	{
		// Pad the key if necessary
		if ($algo === 'sha256') {
			$key = $key . substr($key, 0, 12);
		} elseif ($algo === 'sha512') {
			$key = $key . $key . $key . substr($key, 0, 4);
		}

		// Get the current unix timestamp if one isn't given
		if (is_null($time)) {
			$time = time();
		}
		elseif($time < 0){
			$time = time() + $time;
		}

		// Calculate the count
		$count = (int)floor($time / $time_interval);
		$convert = $this->convertFromSecret($key);

		// Generate a normal HOTP token

		$hex = str_pad(dechex($count), 16, "0", STR_PAD_LEFT);
		$hash = hash_hmac($algo, hex2bin($hex), hex2bin($convert));

		$code = $this->genHTOPValue($hash, $length);

		$code = str_pad((string)$code, $length, "0", STR_PAD_LEFT);
		return substr($code, (-1 * $length));
	}

	private function genHTOPValue($hash, $length)
	{
		// store calculate decimal
		$hmac_result = [];
		// Convert to decimal
		foreach (str_split($hash, 2) as $hex) {
			$hmac_result[] = hexdec($hex);
		}

		$offset = $hmac_result[count($hmac_result)-1] & 0xf;

		$code = (int)($hmac_result[$offset] & 0x7f) << 24
			| ($hmac_result[$offset+1] & 0xff) << 16
			| ($hmac_result[$offset+2] & 0xff) << 8
			| ($hmac_result[$offset+3] & 0xff);

		return $code % pow(10, $length);
	}
	# endregion
	public function convertFromSecret($secret){
		$array = str_split($secret);
		$lambda = function($value){
			return sprintf("%05d", decbin(strpos($this->charset, $value)));
		};
		$binary_string = join("", array_map($lambda, $array));
		$new_string = "";
		for($i = 0; $i < strlen($binary_string); $i+=4){
			$new_string = $new_string . dechex(bindec(substr($binary_string,$i,4)));
		}
		return $new_string;
	}

	/**
	 * Generates several OTPs around a given time.
	 *
	 * @param string $key The secret key.
	 * @param float|int|null $time The time the code should be generated.
	 * @param int $length The length of the code. Usually 6 or 8.
	 * @param int $time_interval The length in between code generation.
	 * @param string $algo The algorithm being used. Default is sha1.
	 * @param int $before The number of codes before this time to be generated. (Should be positive, inclusive).
	 * @param int $after The number of codes after this time to be generated. (Should be positive, inclusive).
	 * @return array
	 */
	public function GenerateCloseTokens(string $key, float|int $time = null, int $length = 6, int $time_interval=30,
										string $algo="sha1", int $before=1, int $after=1): array
	{
		$otps = array();
		if(is_null($time)){
			$time = time();
		}
		for($i=-$before; $i<=$after; $i++){
			$otps[] = $this->GenerateToken($key, $time + ($time_interval * $i), $length, $time_interval, $algo);
		}
		return $otps;
	}
	# endregion
}

// $totp = new Authentication();
// echo $totp->GenerateToken("ACAHAACAAJGILAOC") . PHP_EOL;
// echo $totp->generateQRCode("employee_username1","ACAHAACAAJGILAOC") . PHP_EOL;
// echo $totp->GenerateToken("XE7ZREYZTLXYK444", 1632741679) . PHP_EOL;
