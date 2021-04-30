# Auth Server
This server is responsible for managing and organaizing matches (both practice and tournament) as described in `gameMasterAPI.txt`
It is made out of ? components:

## game_master.db
An SQLite3 dabatabase containing a 2 tables
```
CREATE TABLE "matches" (
	"ID"	INTEGER NOT NULL,
	"gameType"	TEXT NOT NULL,
	"player0"	TEXT NOT NULL,
	"player1"	TEXT NOT NULL,
	"winner"	TEXT NOT NULL,
	"tournament"	TEXT NOT NULL,
	"in_progress"	BOOLEAN NOT NULL DEFAULT 'true',
	PRIMARY KEY("ID" AUTOINCREMENT)
);
CREATE TABLE "tournaments" (
	"name"	TEXT NOT NULL UNIQUE,
	"gameType"	TEXT NOT NULL,
	"place0"	TEXT,
	"place1"	TEXT,
	"place2"	TEXT,
	"place3"	TEXT,
	"finished"	BOOLEAN NOT NULL DEFAULT 'false',
	PRIMARY KEY("name")
);
```
SQLite3 was not chosen for any prarticular reason other than being already familized with it after implemented the auth service
Note: you can access the the DB via gui with `sqlitebrowser game_master.db`

## game_master_db.py
An interface for game_master's DB with all the required functions
```
def check_tournament(name):
def max_match_id():
def create_match(game_type, player0, player1, tournament):
def get_n_wins(username):
def get_n_draws(username):
def get_n_loses(username):
def get_n_tournament_wins(username):
def get_n_practice_wins(username):
def get_n_tournament_draws(username):
def get_n_practice_draws(username):
def get_n_tournament_plays(username):
def get_n_practice_plays(username):
def get_all_plays(username):
def get_all_plays_formated(username):
def get_players_list():
def get_all_players_formated():
def finish_match(match_id):k
def get_all_finished_tournaments():
def insert_tournament(tournament_name, game_type):
def finish_tournament(tournament_name, place0, place1, place2, place3):
def get_all_tournament_matches(tournament_name):
def get_all_tournament_matches_formated(tournament_name):
def get_all_finished_tournaments_formated():
def get_match_winner(match_id):
```

## game_master_mongo_interface.py
An interface for play_master's mongoDB with all the required functions
```
def extract_finished_games():
```

Note: requires the python package `pymongo` (`pip install pymongo`)

## game_master_server.py
An HTTP server handling JSON POST requests for our service (communicates with `game_master_db.py` and `game_master_mongo_interface.py`)
```
POST /newPractice({"username", "gameType"})
POST /cancelNewPractice({"username", "gameType"})
GET  /gameFinished()
POST /getMyScores({"username"})
GET  /getTournaments()
GET  /getAllPlayers()
POST /createTournament({"tournamentName", "gameType"})
```

Also contains a tournament manager 

Note: requires the python package `requests` (`pip install requests`)