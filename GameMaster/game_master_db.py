import sqlite3
import copy


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

#get a list of all the matches the [username]
def get_all_plays(username):
    c.execute("""
        SELECT gameType, player0 as other_player, winner, tournament, in_progress FROM matches WHERE player1=?
        UNION ALL
        SELECT gameType, player1 as other_player, winner, tournament, in_progress FROM matches WHERE player0=?
            """, (username, username,))
    return c.fetchall()

#get a list of all the matches the [username] has finished formated according to POST - /getMyScores 
def get_all_plays_formated(username):
        plays = get_all_plays(username)
        keys = ['gameType', 'opponent', 'score','tournamentName', 'in_progress']
        data = [dict(zip(keys, play)) for play in plays]
        for i in data:
            if i['in_progress'] == 'true':
                i["score"] = '-'
            elif i["score"] == username:
                i["score"] = 3
            elif i["score"] == '':
                i["score"] = 1
            else:
                i["score"] = 0
            del i['in_progress']
        return data
#get a list of the players that have playied a match (even not finished), Note auth.db has a complete list of players
def get_players_list():
    c.execute("""
        SELECT player0 as player FROM matches
        UNION
        SELECT player1 as player FROM matches
        """)
    return c.fetchall()
#get a list of the players that are on the [player_list] formated according to GET - /getAllPlayers
def get_all_players_formated(player_list):
    for i, val in enumerate(player_list):
        player_list[i] = {
            "username": val['username'],
            "practiceScore": get_n_practice_wins(val['username'])*3 + get_n_practice_draws(val['username']),
            "tournamentScore": get_n_tournament_wins(val['username'])*3 + get_n_tournament_draws(val['username'])
        }
    return player_list

#mark match with [match_id] as finished
def finish_match(match_id, winner):
    c.execute("UPDATE matches set winner=?, in_progress='false' where ID=?", (winner, match_id,))
    conn.commit()

def get_all_finished_tournaments():
    c.execute("SELECT * from tournaments WHERE finished='true'")
    return c.fetchall()

def get_all_tournaments():
    c.execute("SELECT * from tournaments")
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
    c.execute("SELECT player1, player0 as player2, winner, in_progress as score from matches WHERE tournament=?", (tournament_name,))
    return c.fetchall()

#get a list of matches of [tournament_name], formated according to GET - /getTournaments
def get_all_tournament_matches_formated(tournament_name):
    keys = ['player1', 'player2', 'score', 'in_progress']
    data = [dict(zip(keys, match)) for match in get_all_tournament_matches(tournament_name)]
    for i in range(len(data)):
        if data[i]['in_progress'] == 'true':
            data[i]['score'] = "-"
        elif data[i]['score'] == data[i]['player1']:
            data[i]['score'] = "3-0"
        elif data[i]['score'] == data[i]['player2']:
            data[i]['score'] = "0-3"
        else:
            data[i]['score'] = "1-1"
        del data[i]['in_progress']
    return data

#formated according to GET - /getTournaments
def get_all_finished_tournaments_formated():
    keys = ['tournamentName', 'game_type', 'place1', 'place2', 'place3', 'place4']
    data = [dict(zip(keys, tourny)) for tourny in get_all_finished_tournaments()]
    for i in range(len(data)):
        data[i]['plays'] = get_all_tournament_matches_formated(data[i]['tournamentName'])
    return data

#formated according to GET - /getTournaments
def get_all_tournaments_formated():
    keys = ['tournamentName', 'game_type', 'place1', 'place2', 'place3', 'place4']
    data = [dict(zip(keys, tourny)) for tourny in get_all_tournaments()]
    for i in range(len(data)):
        data[i]['plays'] = get_all_tournament_matches_formated(data[i]['tournamentName'])
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
    print(get_all_tournament_matches_formated('test'))
    #print(get_all_finished_tournaments_formated())


if __name__ == '__main__':
    main()