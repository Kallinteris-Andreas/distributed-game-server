import sqlite3

#get the current id Counter (aka how many matches have been started)
def max_match_id():
    c.execute("SELECT seq FROM sqlite_sequence WHERE name='matches'")
    return c.fetchone()[0]
def create_match(game_type, player0, player1, tournament):
        c.execute("INSERT INTO matches (gameType, player0, player1, winner, tournament) VALUES (?, ?, ?, '', ?)", (game_type, player0, player1, tournament,))
        conn.commit()
#get the number of wins/draws of [username]
def get_n_wins(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner=?", (username, username, username,))
    return c.fetchone()[0]
def get_n_draws(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner=''", (username, username,))
    return c.fetchone()[0]
def get_n_loses(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner!='' AND winner!='tiki'", (username, username,))
    return c.fetchone()[0]
def get_n_tournament_wins(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner=? AND tournament!=''", (username, username, username,))
    return c.fetchone()[0]
def get_n_practice_wins(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner=? AND tournament=''", (username, username, username,))
    return c.fetchone()[0]
def get_n_tournament_draws(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner='' AND tournament!=''", (username, username,))
    return c.fetchone()[0]
def get_n_practice_draws(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner='' AND tournament=''", (username, username,))
    return c.fetchone()[0]
def get_n_tournament_plays(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND tournament!=''", (username, username,))
    return c.fetchone()[0]
def get_n_practice_plays(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND tournament=''", (username, username,))
    return c.fetchone()[0]
def get_all_plays(username):
    c.execute("""
        SELECT gameType, player0 as other_player, winner, tournament FROM matches WHERE player1=? AND in_progress='false'
        UNION ALL
        SELECT gameType, player1 as other_player, winner, tournament FROM matches WHERE player0=? AND in_progress='false'
            """, (username, username,))
    return c.fetchall()
def get_all_plays_formated(username):
        plays = get_all_plays(username)
        keys = ['gameType', 'opponent', 'score','tournamentName']
        data = [dict(zip(keys, play)) for play in plays]
        for i in data:
            if i["score"] == username:
                i["score"] = 3
            elif i["score"] == '':
                i["score"] = 1
            else:
                i["score"] = 0
        return data
def get_players_list():
    c.execute("""
        SELECT player0 as player FROM matches
        UNION
        SELECT player1 as player FROM matches
        """)
    return c.fetchall()
def get_all_players_formated():
    player_list = get_players_list()
    for i, val in enumerate(player_list):
        player_list[i] = {
            "username": val[0],
            "practiceScore": get_n_practice_wins(val[0])*3 + get_n_practice_draws(val[0]),
            "tournamentScore": get_n_tournament_wins(val[0])*3 + get_n_tournament_draws(val[0])
        }
    return player_list


conn = sqlite3.connect("game_master.db")
c = conn.cursor()

def main():
    pass
    #print(get_n_wins('tiki'))
    #print(get_n_draws('tiki'))
    #print(get_n_tournament_wins('tiki'))
    #print(get_n_tournament_draws('tiki'))
    #print(get_n_practice_wins('tiki'))
    #print(get_n_practice_draws('tiki'))
    #print(get_n_practice_plays('tiki'))
    #print(get_n_tournament_plays('tiki'))
    #print(get_n_wins('tiki'))
    #print(get_n_draws('tiki'))
    #print(get_n_loses('tiki'))
    #print(get_all_plays('tiki'))
    #print(get_all_plays_formated('tiki'))
    #print(get_players_list())
    #print(get_all_players_formated())


if __name__ == '__main__':
    main()