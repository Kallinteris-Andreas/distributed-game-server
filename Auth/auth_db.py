import sqlite3
import exrex

#Checks if the [Username] is in the DB
def check_username(username):
    c.execute("SELECT EXISTS(SELECT * FROM users WHERE Username=?)", (username,))
    return c.fetchone()[0] != 0
#Matches [username] has [password] and returns True if it matches else returns False
def match_user_pass(username, password):
    c.execute("SELECT EXISTS(SELECT * FROM users WHERE Username=? AND Password=?)", (username, password))
    return c.fetchone()[0] != 0
def check_token(token):
    c.execute("SELECT EXISTS(SELECT * FROM users WHERE Token=?)", (token,))
    return c.fetchone()[0] != 0


#creates a user with [username] and [password], returs False on fail and True on success
def create_user(username, password):
    if check_username(username): #if username is in the table ignore request
        return False
    c.execute("INSERT INTO users VALUES (?, ?, NULL, '100')", (username, password,))
    conn.commit()
    if not check_username(username):
        return False
    return True

def change_password(token, new_password):
    c.execute("UPDATE users SET Password=? WHERE Token=?", (new_password, token,))

#returns [token] and [role] if [username] and [password] match else returns False
def login(username, password):
    if not match_user_pass(username, password):
        return False
    token = exrex.getone('[a-zA-Z0-9]{32}')
    c.execute("UPDATE users SET Token=? WHERE Username=?", (token, username))
    c.execute("SELECT Role FROM users WHERE Username=?", (username,))
    role = c.fetchone()[0]
    conn.commit()
    return token, role

#returns the [username] and [role] of the [token], on fail it returns false
def validate_token(token):
    c.execute("SELECT EXISTS(SELECT * FROM users WHERE Token=?)", (token,))
    if c.fetchone()[0] == 0:
        return False
    c.execute("SELECT Username FROM users WHERE Token=?", (token,))
    username = c.fetchone()[0]
    c.execute("SELECT Role FROM users WHERE Token=?", (token,))
    role = c.fetchone()[0]
    return username, role

def logout(token):
    if not check_token(token):
        return False
    c.execute("UPDATE users SET Token=NULL WHERE Token=?", (token,))
    conn.commit()
    return True

#Note: Checking That repsonsibility is done by outside this service
def update_role(username, new_role):
    if not check_username(username):
        return False
    c.execute("UPDATE users SET Role=? WHERE Username=?", (new_role, username))
    conn.commit()
    return True

def list_users():
    c.execute("SELECT Username, Role FROM users")
    list = c.fetchall()
    return list

def db_unit_test():
    assert check_username("Andy") == True
    assert check_username("Invalid User Name") == False
    assert match_user_pass('Andy', 'Admin') == True
    assert match_user_pass('Andy', 'Wrong Password') == False 
    assert login('Andy', 'Admin')[0] == "93cb0c64212c3491f14ba3078cbc7f6e"
    assert login('Andy', 'Admin')[1] == "001"
    assert login('Andy', 'Wrong Pass') == False
    assert validate_token('93cb0c64212c3491f14ba3078cbc7f6e')[0] == 'Andy'
    assert validate_token('93cb0c64212c3491f14ba3078cbc7f6e')[1] == '001'
    assert validate_token('Invalid Token') == False
    assert logout('Invalid Token') == False

conn = sqlite3.connect("auth.db")
c = conn.cursor()