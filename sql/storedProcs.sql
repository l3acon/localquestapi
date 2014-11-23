
DROP PROCEDURE IF EXISTS find_locals;
DROP PROCEDURE IF EXISTS create_user;
DROP PROCEDURE IF EXISTS create_game;

--
-- stored procedures
--

CREATE PROCEDURE find_locals (IN APIKey VARCHAR(64), Token VARCHAR(64), DistanceMax DECIMAL)
		PROC:BEGIN
		IF (APIKey IS NULL)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'APIKey must not be NULL.';
			LEAVE PROC;
		END IF;

		IF (Token IS NULL)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Token must not be NULL.';
			LEAVE PROC;
		END IF;

		IF (DistanceMax IS NULL)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'DistanceMax must not be NULL.';
			LEAVE PROC;
		END IF;

		SELECT uge2.userToken
		FROM userGameEx uge1
			INNER JOIN userDistanceEx ude ON uge1.userId = ude.userId1
			INNER JOIN userGameEx uge2 ON uge2.userId = ude.userId2
		WHERE uge1.gameAPI = APIKey
				AND uge1.userToken = Token
				AND uge2.gameAPI = APIKey
				AND ude.distance <= DistanceMax;
	END;

CREATE PROCEDURE create_user (IN APIKey VARCHAR(64), Mail VARCHAR(256), Lat DECIMAL(8,5), Lon DECIMAL(8,5))
		PROC:BEGIN
		DECLARE newGameID BIGINT UNSIGNED;
		DECLARE newUserID BIGINT UNSIGNED;
		DECLARE newToken VARCHAR(64);

		IF (APIKey IS NULL)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'APIKey must not be NULL.';
			LEAVE PROC;
		END IF;

		IF (Mail IS NULL)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mail must not be NULL.';
			LEAVE PROC;
		END IF;

		IF (Lat IS NULL)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Lat must not be NULL.';
			LEAVE PROC;
		END IF;

		IF (Lon IS NULL)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Lon must not be NULL.';
			LEAVE PROC;
		END IF;

		SET newGameID = (SELECT gameId FROM game WHERE gameAPI = APIKey);
		SET newUserID = (SELECT userId FROM user WHERE email = Mail);

		IF (newGameID IS NULL)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No matching game found for APIKey.';
			LEAVE PROC;
		END IF;

-- If user does not exist insert into user
		IF (newUserID IS NULL)
		THEN
			INSERT INTO user (email, latitude, longitude)
			VALUES (Mail, Lat, Lon);

			SET newUserID = (SELECT userId FROM user WHERE email = Mail);

-- TODO FIX HORRIBLE KLUDGE JOIN HERE...
			INSERT INTO userDistance (userId1, userId2, distance)
				SELECT u1.userId, u2.userId, sqrt((u1.latitude - u2.latitude)^2 + (u1.longitude - u2.longitude)^2)
				FROM user u1
					INNER JOIN user u2 ON u2.latitude BETWEEN u1.latitude - 1 AND u1.latitude + 1 AND u2.longitude BETWEEN u1.longitude - 1 AND u1.longitude + 1
				WHERE u1.userId = newUserID
						AND u1.userId <> u2.userId;
		END IF;

-- If user not associate with calling game add user to game
		IF NOT EXISTS (SELECT 1 FROM userGame WHERE gameId = newGameID AND userId = newUserID)
		THEN
			INSERT INTO userGame (userId, gameId, userToken)
			VALUES (newUserID, newGameID, sha2(CONCAT(Mail, FLOOR(1 + (RAND() * 4294967296))),256));
		END IF;

		SELECT userToken
		FROM userGame
		WHERE gameId = newGameID
				AND userId = newUserID
		ORDER BY userGameId
		LIMIT 1;
	END;

CREATE PROCEDURE create_game (IN newGameDesc VARCHAR(256))
		PROC:BEGIN
		IF EXISTS (SELECT 1 FROM game WHERE gameDesc = newGameDesc)
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Game already exists in database.';
			LEAVE PROC;
		ELSE
			INSERT INTO game (gameAPI, gameDesc)
			VALUES (sha2(CONCAT(newGameDesc, FLOOR(1 + (RAND() * 4294967296))),256), newGameDesc);
		END IF;
		SELECT gameAPI
		FROM game
		WHERE gameDesc = newGameDesc;
	END