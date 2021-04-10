# Auth Server
This server is responsible for the authentication service
It is made out of 3 components:

## Auth.db
An SQLite3 dabatabase containing a single table with 4 columns as shown below:
```
CREATE TABLE "users" (
	"Username"	TEXT NOT NULL UNIQUE,
	"Password"	TEXT NOT NULL DEFAULT 1234,
	"Token"	CHAR(32) NOT NULL UNIQUE,
	"Role"	CHAR(3) NOT NULL DEFAULT 100,
	PRIMARY KEY("Username")
);
```

## Auth_db.py
An interface for our DB with all the required functions
```
def check_username(username):
def match_user_pass(username, password):
def check_username(username):
def create_user(username, password):
def change_password(token, new_password):
def login(username, password):
def validate_token(token):
def db_unit_test():
```

## Auth_server.py
An HTTP server handling JSON POST requests for our service (communicates with auth_db.py)
```
POST /createUser({"username", "password"})
POST /changePassword({"token", "password"})
POST /login({"username", "password"})
POST /validateToken({"token"})
```
