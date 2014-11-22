--
-- drop tables if they exist in the database
-- these are dropped in FK > PK order
--

DROP TABLE IF EXISTS userDistance;
DROP TABLE IF EXISTS location;
DROP TABLE IF EXISTS userGame;
DROP TABLE IF EXISTS game;
DROP TABLE IF EXISTS user;


--
-- create tables
-- these are created in PK > FK order
--

CREATE TABLE user (
	userId BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	email VARCHAR(250) NOT NULL,
	latitude DECIMAL(8, 5) NOT NULL,
	longitude DECIMAL(8, 5) NOT NULL,
	PRIMARY KEY (userId),
	UNIQUE (email)
);

CREATE TABLE game (
	gameId BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	gameAPI VARCHAR(64) NOT NULL,
	gameDesc VARCHAR(250) NOT NULL,
	PRIMARY KEY (gameId),
	UNIQUE (gameAPI),
	INDEX (gameDesc)
);

CREATE TABLE userGame (
	userGameId BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	userId BIGINT UNSIGNED NOT NULL,
	gameId BIGINT UNSIGNED NOT NULL,
	userToken VARCHAR(64) NOT NULL,
	PRIMARY KEY (userGameId),
	INDEX (userId),
	INDEX (gameId),
	INDEX (userId, gameId),
	INDEX (gameId, userId),
	FOREIGN KEY (userId) REFERENCES user(userId),
	FOREIGN KEY (gameId) REFERENCES game(gameId)
);

CREATE TABLE userDistance (
	userDistanceId BIGINT UNSIGNED AUTO_INCREMENT  NOT NULL,
	userId1 BIGINT UNSIGNED NOT NULL,
	userId2 BIGINT UNSIGNED NOT NULL,
	distance DECIMAL(8, 5) NOT NULL,
	PRIMARY KEY (userDistanceId),
	INDEX (userId1),
	INDEX (userId2),
	INDEX (userId1, userId2),
	INDEX (userId2, userId1),
	FOREIGN KEY (userId1) REFERENCES user(userId),
	FOREIGN KEY (userId2) REFERENCES user(userId)
);


--
-- create view
--

CREATE OR REPLACE VIEW userDistanceEx
AS
	SELECT userId1, userId2, distance
	FROM userDistance
	UNION
	SELECT userId2, userId1, distance
	FROM userDistance;

CREATE OR REPLACE VIEW userGameEx
AS
	SELECT gameAPI, userToken, userId
	FROM game
		INNER JOIN userGame ON game.gameId = userGame.gameId;

--
-- stored procedures
--

CREATE PROCEDURE find_locals (IN APIKey VARCHAR(64), Token VARCHAR(64), DistanceMax DECIMAL)
	BEGIN
-- TODO add in validation of incoming variables

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
	BEGIN
		DECLARE newGameID BIGINT UNSIGNED;
		DECLARE newUserID BIGINT UNSIGNED;

-- TODO add in validation of incoming variables

		SET newGameID = (SELECT gameId FROM game WHERE gameAPI = APIKey);
		SET newUserID = (SELECT userId FROM user WHERE email = Mail);

-- TODO THROW NASTY ERROR WHEN THE gameAPI IS NULL.

-- If user does not exist insert into user
		IF (newUserID IS null)
		THEN
			INSERT INTO user (email, latitude, longitude)
			VALUES (Mail, Lat, Lon);

			SET newUserID = (SELECT userId FROM user WHERE email = Mail);
		END IF;

-- If user not associate with calling game add user to game
		IF NOT EXISTS (SELECT 1 FROM userGame WHERE gameId = newGameID AND userId = newUserID)
		THEN
			DECLARE newToken VARCHAR(64);
			SET newToken = sha2(Mail + FLOOR(1 + (RAND() * 4294967296)), 256);

			INSERT INTO userGame (userId, gameId, userToken)
			VALUES (newUserID, newGameID, newToken);
		END IF;

		SELECT userToken
		FROM userGame
		WHERE gameId = newGameID
				AND userId = newUserID
		ORDER BY userGameId
		LIMIT 1;
	END