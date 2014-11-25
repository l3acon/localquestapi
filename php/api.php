<?php
/**
 * @author Marc Hayes <Marc.Hayes.Tech@gmail.com>
 */
require_once('../php/STUFF.php');

/**
 * Class Create_Game
 */
class API {
	/**
	 * @param $gameDesc
	 * @return array
	 * @throws RangeException
	 * @throws UnexpectedValueException
	 * @throws mysqli_sql_exception
	 */
	public static function createGame ($gameDesc) {
		// enforce that $gameDesc is NOT null
		if($gameDesc === null) {
			throw(new UnexpectedValueException("gameDesc must not be null"));
		}

		// ensure that $gameDesc is an string
		$gameDesc = trim($gameDesc);
		if(($gameDesc = filter_var($gameDesc, FILTER_SANITIZE_STRING)) === false) {
			throw(new UnexpectedValueException("gameDesc $gameDesc is not a string"));
		}

		// ensure that $gameDesc will not be truncated
		if(strlen($gameDesc) > 256) {
			throw(new RangeException("gameDesc must be less then 256 characters in length."));
		}

		// create query template
		$query = "CALL create_game($gameDesc)";

		$results = API::db_all($query);

		return($results);
	}

	/**
	 * @param $gameAPI
	 * @param $email
	 * @param $latitude
	 * @param $longitude
	 * @return string
	 * @throws RangeException
	 * @throws UnexpectedValueException
	 * @throws mysqli_sql_exception
	 */
	public static function createUser ($gameAPI, $email, $latitude, $longitude) {
		// enforce that $gameAPI is NOT null
		if($gameAPI === null) {
			throw(new UnexpectedValueException("gameDesc must not be null"));
		}

		// ensure that $gameAPI is an string
		$gameAPI = trim($gameAPI);
		if(ctype_xdigit($gameAPI) === false) {
			throw(new UnexpectedValueException("gameAPI $gameAPI is not a hex string"));
		}

		// ensure that $gameAPI will not be truncated
		if(strlen($gameAPI) > 64) {
			throw(new RangeException("gameDesc must be less then 64 characters in length."));
		}

		// enforce that $email is NOT null
		if($email === null) {
			throw(new UnexpectedValueException("email must not be null"));
		}

		// ensure that $email is an string
		$email = trim($email);
		if(($email = filter_var($email, FILTER_VALIDATE_EMAIL)) === false) {
			throw(new UnexpectedValueException("email $email is not a valid email"));
		}

		// enforce DECIMAL(8,5) format for $latitude
		if(preg_match("^(\d{1,3})\.\d{1,5}$", $latitude, $matches) === false) {
			throw(new UnexpectedValueException("latitude $latitude is not a valid decimal"));
		}

		// lets make sure the earth is in fact round
		if ($matches[0] >= 90 || $matches[0] <= -90){
			throw(new RangeException("latitude $latitude is not a valid latitude"));
		}

		// enforce DECIMAL(8,5) format for $longitude
		if(preg_match("^(\d{1,3})\.\d{1,5}$", $longitude, $matches) === false) {
			throw(new UnexpectedValueException("longitude $longitude is not a valid decimal"));
		}

		// lets make sure the earth is in fact round in both dimensions
		if ($matches[0] >= 90 || $matches[0] <= -90){
			throw(new RangeException("longitude $longitude is not a valid longitude"));
		}

		// create query template
		$query = "CALL create_user('$gameAPI','$email',$latitude,$longitude);";

		$results = API::db_all($query);

		$results =  API::array2json($results);

		return($results);
	}

	/**
	 * @param $gameAPI
	 * @param $token
	 * @param $distanceMax
	 * @return array
	 * @throws RangeException
	 * @throws UnexpectedValueException
	 * @throws mysqli_sql_exception
	 */
	public static function findLocals ($gameAPI, $token, $distanceMax) {
		// enforce that $gameAPI is NOT null
		if($gameAPI === null) {
			throw(new UnexpectedValueException("gameDesc must not be null"));
		}

		// ensure that $gameAPI is an string
		$gameAPI = trim($gameAPI);
		if(ctype_xdigit($gameAPI) === false) {
			throw(new UnexpectedValueException("gameAPI $gameAPI is not a hex string"));
		}

		// ensure that $gameAPI will not be truncated
		if(strlen($gameAPI) > 64) {
			throw(new RangeException("gameDesc must be less then 64 characters in length."));
		}

		// enforce that $token is NOT null
		if($token === null) {
			throw(new UnexpectedValueException("token must not be null"));
		}

		// ensure that $gameAPI is an string
		$token = trim($token);
		if(ctype_xdigit($token) === false) {
			throw(new UnexpectedValueException("token $token is not a hex string"));
		}

		// ensure that $gameAPI will not be truncated
		if(strlen($token) > 64) {
			throw(new RangeException("$token must be less then 64 characters in length."));
		}

		// enforce that distance max is a valid decimal
		if(is_float($distanceMax) === false) {
			throw(new UnexpectedValueException("distanceMax $distanceMax is not a valid decimal"));
		}

		// create query template
		$query = "CALL find_locals('$gameAPI','$token',$distanceMax)";

		$results = API::db_all($query);

		$results = json_encode($results);

		return($results);
	}

	/**
	 * @param $query
	 * @return array
	 * @throws mysqli_sql_exception
	 */
	private static function db_all($query)
	{
		//Open connection
		$link = MysqliConfiguration::getMysqli();

		$array = array();

		// Execute multi query
		if (($hmm = mysqli_multi_query($link,$query)) !== false)
		{
			do
			{
				$count = 0;
				// Store result set
				if ($result=mysqli_store_result($link))
				{
					while ($row=mysqli_fetch_assoc($result))
					{
						$array[$count] = $row;
						$count++;
					}
					mysqli_free_result($result);
				}
			}
			while (mysqli_next_result($link));
		} else {
			throw(new mysqli_sql_exception("Derp (".$link->errono.")".$link->error));
		}

		mysqli_close($link);

		return($array);
	}
}