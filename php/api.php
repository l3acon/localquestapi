<?php
/**
 * @author Marc Hayes <Marc.Hayes.Tech@gmail.com>
 */

/**
 * Class Create_game
 */
class Create_game {
	private $gameAPI;

	/**
	 * @param $gameAPI
	 */
	function __construct($gameAPI)
	{
		$this->gameAPI = $gameAPI;
	}

	/**
	 * @return mixed
	 */
	public function getGameAPI()
	{
		return $this->gameAPI;
	}

	/**
	 * @param mixed $gameAPI
	 */
	public function setGameAPI($gameAPI)
	{
		// TODO validate this
		$this->gameAPI = $gameAPI;
	}

	/**
	 * @param $mysqli
	 * @param $gameDesc
	 * @return Create_game|null
	 */
	public static function createGame ($mysqli, $gameDesc) {
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("Input is not a valid mysqli object"));
		}

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
		$query = "CALL create_game(?);";

		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("Unable to prepare statement"));
		}

		// bind the variables to the place holders in the template
		$wasClean = $statement->bind_param("s", $gameDesc);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("Unable to bind parameters"));
		}

		// execute the statement
		$result = $statement->execute();
		if($result === false) {
			throw(new mysqli_sql_exception("Unable to execute mySQL statement"));
		}

		// since this is unique this will return only 1 row
		$row = $result->fetch_assoc();

		//convert assoc array to Create_game object
		if($row !== null) {
			try {
				$game = new Create_game($row["gameAPI"]);
			} catch(Exception $exception) {
				// if the row could not be converted throw it
				throw(new mysqli_sql_exception("Unable to process result set"));
			}
			// if we got here, the Create_game is good
			return($game);
		} else {
			// no result found return null
			return(null);
		}
	}
}


/**
 * Class Create_user
 */
class Create_user {
	private $userToken;

	/**
	 * @param $userToken
	 */
	function __construct($userToken)
	{
		$this->userToken = $userToken;
	}

	/**
	 * @return mixed
	 */
	public function getUserToken()
	{
		return $this->userToken;
	}

	/**
	 * @param mixed $userToken
	 */
	public function setUserToken($userToken)
	{
		// TODO validate this
		$this->userToken = $userToken;
	}

	/**
	 * @param $mysqli
	 * @param $gameAPI
	 * @param $email
	 * @param $latitude
	 * @param $longitude
	 * @return Create_user|null
	 */
	public static function createUser ($mysqli, $gameAPI, $email, $latitude, $longitude) {
		// TODO implement PHP side of create_user SQL stored proc
		//CALL create_user(gameAPI, 'Marc.Hayes.Tech@Gmail.com', 35.1107, -106.6100);
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("Input is not a valid mysqli object"));
		}

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
		$query = "CALL create_user(?,?,?,?);";

		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("Unable to prepare statement"));
		}

		// bind the variables to the place holders in the template
		$wasClean = $statement->bind_param("ssdd", $gameAPI, $email, $latitude, $longitude);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("Unable to bind parameters"));
		}

		// execute the statement
		$result = $statement->execute();
		if($result === false) {
			throw(new mysqli_sql_exception("Unable to execute mySQL statement"));
		}

		// since this is unique this will return only 1 row
		$row = $result->fetch_assoc();

		//convert assoc array to Create_user object
		if($row !== null) {
			try {
				$user = new Create_user($row["userToken"]);
			} catch(Exception $exception) {
				// if the row could not be converted throw it
				throw(new mysqli_sql_exception("Unable to process result set"));
			}
			// if we got here, the Create_user is good
			return($user);
		} else {
			// no result found return null
			return(null);
		}
	}
}


/**
 * Class Find_Locals
 */
class Find_Locals {
	private $userToken;

	/**
	 * @param $userToken
	 */
	function __construct($userToken)
	{
		$this->userToken = $userToken;
	}

	/**
	 * @return mixed
	 */
	public function getLocals()
	{
		return $this->$userToken;
	}

	/**
	 * @param $userToken
	 */
	public function setLocals($userToken)
	{
		// TODO validate this
		$this->userToken = $userToken;
	}

	/**
	 * @param $mysqli
	 * @param $gameAPI
	 * @param $token
	 * @param $distanceMax
	 * @return null
	 */
	public static function findLocals ($mysqli, $gameAPI, $token, $distanceMax) {
		// TODO implement PHP side of find_locals SQL stored proc
		// handle degenerate cases
		if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
			throw(new mysqli_sql_exception("Input is not a valid mysqli object"));
		}

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
		$query = "CALL create_user(?,?,?);";
		$statement = $mysqli->prepare($query);
		if($statement === false) {
			throw(new mysqli_sql_exception("Unable to prepare statement"));
		}

		// bind the variables to the place holders in the template
		$wasClean = $statement->bind_param("ssd", $gameAPI, $token, $distanceMax);
		if($wasClean === false) {
			throw(new mysqli_sql_exception("Unable to bind parameters"));
		}

		// execute the statement
		$result = $statement->execute();
		if($result === false) {
			throw(new mysqli_sql_exception("Unable to execute mySQL statement"));
		}

		// process results
		$results = $statement->get_result();
		if($results->num_rows > 0) {
			// retrieve results in bulk into an array
			$results = $results->fetch_all(MYSQL_ASSOC);
			if($results === false) {
				throw(new mysqli_sql_exception("Unable to process result set"));
			}

			// step through results array and convert to Find_Locals objects
			foreach ($results as $index => $row) {
				$results[$index] = new Find_Locals($row["userToken"]);
			}

			// return resulting array of Find_Locals objects
			return($results);
		} else {
			return(null);
		}
	}
}