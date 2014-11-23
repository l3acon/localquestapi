CALL create_game('Local Quest');

CALL create_user((SELECT gameAPI FROM game WHERE gameDesc = 'Local Quest'), 'Marc.Hayes.Tech@Gmail.com', 35.1107, -106.6100);
CALL create_user((SELECT gameAPI FROM game WHERE gameDesc = 'Local Quest'), '1@Gmail.com', 35.6107, -106.0100);
