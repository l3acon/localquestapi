USE localQuestAPI;
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
	UNIQUE (gameDesc)
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

