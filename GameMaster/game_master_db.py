import sqlite3


#Checks if the [name] is in the tournament Table
def check_tournament(name):
    c.execute("SELECT EXISTS(SELECT * FROM tournaments WHERE name=?)", (name,))
    return c.fetchone()[0] != 0

#get the current id Counter (aka how many matches have been started)
def max_match_id():
    c.execute("SELECT seq FROM sqlite_sequence WHERE name='matches'")
    return c.fetchone()[0]

#log the creating of a match in our DB
def create_match(game_type, player0, player1, tournament):
        c.execute("INSERT INTO matches (gameType, player0, player1, winner, tournament) VALUES (?, ?, ?, '', ?)", (game_type, player0, player1, tournament,))
        conn.commit()

#get the number of plays/wins/draws/loses of [username] for {any/tournament/practice play}
def get_n_wins(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner=?", (username, username, username,))
    return c.fetchone()[0]
def get_n_draws(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner=''", (username, username,))
    return c.fetchone()[0]
def get_n_loses(username):
    c.execute("SELECT COUNT(*) FROM matches WHERE (player0=? OR player1=?) AND in_progress='false' AND winner!='' AND winner!=?", (username, username, username))
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

#get a list of all the matches the [username] has finished 
def get_all_plays(username):
    c.execute("""
        SELECT gameType, player0 as other_player, winner, tournament FROM matches WHERE player1=? AND in_progress='false'
        UNION ALL
        SELECT gameType, player1 as other_player, winner, tournament FROM matches WHERE player0=? AND in_progress='false'
            """, (username, username,))
    return c.fetchall()

#get a list of all the matches the [username] has finished formated according to POST - /getMyScores 
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
#get a list of the players that have playied a match (even not finished), Note auth.db has a complete list of players
def get_players_list():
    c.execute("""
        SELECT player0 as player FROM matches
        UNION
        SELECT player1 as player FROM matches
        """)
    return c.fetchall()
#get a list of the players that have playied a match (even not finished) formated according to GET - /getAllPlayers
def get_all_players_formated():
    player_list = get_players_list()
    for i, val in enumerate(player_list):
        player_list[i] = {
            "username": val[0],
            "practiceScore": get_n_practice_wins(val[0])*3 + get_n_practice_draws(val[0]),
            "tournamentScore": get_n_tournament_wins(val[0])*3 + get_n_tournament_draws(val[0])
        }
    return player_list

#mark match with [match_id] as finished
def finish_match(match_id, winner):
    c.execute("UPDATE matches set winner=?, in_progress='false' where ID=?", (winner, match_id,))
    conn.commit()

def get_all_finished_tournaments():
    c.execute("SELECT * from tournaments WHERE finished='true'")
    return c.fetchall()

def insert_tournament(tournament_name, game_type):
    c.execute("INSERT INTO tournaments(name, gameType) VALUES (?, ?)", (tournament_name, game_type))
    conn.commit()

#mark tourament with [tournament_name] as finished and store winners
def finish_tournament(tournament_name, place0, place1, place2, place3):
    c.execute("UPDATE tournaments SET place0=?, place1=?, place2=?, place3=?, finished='true' WHERE name=?", (place0, place1, place2, place3, tournament_name,))
    conn.commit()

#get a list of matches of [tournament_name]
def get_all_tournament_matches(tournament_name):
    c.execute("SELECT player1, player0 as player2, winner as score from matches WHERE tournament=?", (tournament_name,))
    return c.fetchall()

#get a list of matches of [tournament_name], formated according to GET - /getTournaments
def get_all_tournament_matches_formated(tournament_name):
    keys = ['player1', 'player2', 'score']
    data = [dict(zip(keys, match)) for match in get_all_tournament_matches(tournament_name)]
    for i in range(len(data)):
        if data[i]['score'] == data[i]['player1']:
            data[i]['score'] = "3-0"
        elif data[i]['score'] == data[i]['player1']:
            data[i]['score'] = "0-3"
        else:
            data[i]['score'] = "1-1"
    return data
#formated according to GET - /getTournaments
def get_all_finished_tournaments_formated():
    keys = ['name', 'game_type', 'place1', 'place2', 'place3', 'place4']
    data = [dict(zip(keys, tourny)) for tourny in get_all_finished_tournaments()]
    for i in range(len(data)):
        data[i]['plays'] = get_all_tournament_matches_formated(data[i]['name'])
    return data

#returns the winner of match with [match_id]
def get_match_winner(match_id):
    c.execute("SELECT winner FROM matches where ID = ?", (match_id,))
    return c.fetchone[0]


conn = sqlite3.connect("game_master.db", check_same_thread=False)
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
    #finish_match(17)
    #print(get_all_finished_tournaments())
    #insert_tournament('test', 'chess')
    #print(get_all_tournament_matches('test'))
    #print(get_all_tournament_matches_formated('test'))
    #print(get_all_finished_tournaments_formated())


if __name__ == '__main__':
    main()