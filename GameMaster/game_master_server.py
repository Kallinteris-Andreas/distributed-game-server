from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import requests
import time
import threading
import game_master_db
import game_master_mongo_interface

practice_chess_waiting_list = None
practice_ttt_waiting_list = None
NO_TOURNAMENT = ''
play_master_url = "http://127.0.0.1:8083/"
auth_url = "http://127.0.0.1:8081/"

def create_play(game_type, username0, username1, tournament_name):
    game_master_db.create_match(game_type, username0, username1, tournament_name)
    match_id = game_master_db.max_match_id()
    data = {'playId': match_id, 'player1': username0, "player2": username1, "gameType": game_type, "tournamentName" : ""}
    json_data = json.dumps(data, indent = 4)
    r = requests.post(url = play_master_url + "createPlay", data = json_data)
    while not r.ok:
        r = requests.post(url = play_master_url + "createPlay", data = json_data)
    return match_id

def list_of_players():
    r = requests.get(url = auth_url + "listPlayers")
    while not r.ok:
        r = requests.get(url = auth_url + "listPlayers")
    return json.loads(r.text)

remaining_matches_of_tourny = {} #matches left for this round of play, once empty start next round
remaining_players_of_tourny = {}
def manage_tournament(tournament_name, game_type):
    global remaining_players_of_tourny
    remaining_players_of_tourny[tournament_name] = []
    for i in list_of_players():
        remaining_players_of_tourny[tournament_name].append(i['username'])
    #print("starting tournament with:"
    #print(remaining_players_of_tourny[tournament_name])

    remaining_matches_of_tourny[tournament_name] = []
    place1 = '' #TODO
    place2 = ''
    place3 = ''
    while len(remaining_players_of_tourny[tournament_name]) != 1:
        for i in range(0, int(len(remaining_players_of_tourny[tournament_name])/2)):
            match_id = create_play(game_type, remaining_players_of_tourny[tournament_name][i], remaining_players_of_tourny[tournament_name][i+1], tournament_name)
            remaining_matches_of_tourny[tournament_name].append(match_id)

        players_of_previous_round = remaining_players_of_tourny[tournament_name]
        if (len(remaining_players_of_tourny[tournament_name])%2 == 1):#if odd number of contesties left grant a bye to someone
            remaining_players_of_tourny[tournament_name] = [remaining_players_of_tourny[tournament_name].pop()]
        else:
            remaining_players_of_tourny[tournament_name] = []
        
        while len(remaining_matches_of_tourny) != 0: # wait till the matches all over
            #print("tournament: " + tournament_name + " is waiting for all the matches to finish")
            time.sleep(5)
            
    place0 = remaining_players_of_tourny[tournament_name][0]
    
    game_master_db.finish_tournament(tournament_name, place0, place1, place2, place3)
    
    del remaining_matches_of_tourny[tournament_name]
    del remaining_players_of_tourny[tournament_name]

    


class game_master_handler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path.endswith('/gameFinished'):
            finished_games = game_master_mongo_interface.extract_finished_games()
            for game in finished_games:
                game_master_db.finish_match(game[0], game[1])
                for tournament_name in remaining_matches_of_tourny:#handle case of tournament match
                    if game[0] in remaining_matches_of_tourny[tournament_name]:
                        remaining_matches_of_tourny[tournament_name].remove(game[0])
                        if game[1] != '':
                            remaining_players_of_tourny[tournament_name].append(game[1])
                            #TODO? what about draws
            self.send_response(200)
            self.end_headers()
        elif self.path.endswith('/getTournaments'):
            response = json.dumps(game_master_db.get_all_finished_tournaments_formated(), indent=4)
            self.send_response(200)
            self.send_header('Content-type','application/json')
            self.end_headers()
            self.wfile.write(response.encode("utf-8"))
        if self.path.endswith('/getAllPlayers'):
            response = json.dumps(game_master_db.get_all_players_formated(), indent=4)
            self.send_response(200)
            self.send_header('Content-type','application/json')
            self.end_headers()
            self.wfile.write(response.encode("utf-8"))
    def do_POST(self):
        global practice_chess_waiting_list
        global practice_ttt_waiting_list
        content_len = int(self.headers.get('Content-Length'))
        post_body = self.rfile.read(content_len)
        if content_len > 0:
            json_body = json.loads(post_body)
        if self.path.endswith('/newPractice'):
            username = (json_body['username'])
            game_type = (json_body['gameType'])
            if game_type == "chess":
                if practice_chess_waiting_list == None:
                    practice_chess_waiting_list = username
                    self.send_response(200)
                    self.end_headers()
                elif practice_chess_waiting_list == username:
                    self.send_response(200)
                    self.end_headers()
                else:
                    username1 = practice_chess_waiting_list
                    practice_chess_waiting_list = None
                    create_play(game_type, username, username1, NO_TOURNAMENT)
                    self.send_response(200)
                    self.end_headers()
            elif game_type == "tictactoe":
                if practice_ttt_waiting_list == None:
                    practice_ttt_waiting_list = username
                    self.send_response(200)
                    self.end_headers()
                elif practice_ttt_waiting_list == username:
                    self.send_response(200)
                    self.end_headers()
                else:
                    username1 = practice_ttt_waiting_list
                    practice_ttt_waiting_list = None
                    create_play(game_type, username, username1, NO_TOURNAMENT)
                    self.send_response(200)
                    self.end_headers()
            else:
                self.send_response(500)
                self.end_headers()
        elif self.path.endswith('/cancelNewPractice'):
            username = (json_body['username'])
            game_type = (json_body['gameType'])
            if game_type == 'chess':
                if practice_chess_waiting_list == username:
                    practice_chess_waiting_list = None
            elif game_type == 'tictactoe':
                if practice_ttt_waiting_list == username:
                    practice_ttt_waiting_list = None
            else:
                self.send_response(500)
                self.end_headers()
                return
            self.send_response(200)
            self.end_headers()
        elif self.path.endswith('/getMyScores'):
            username = (json_body['username'])
            practice_score = game_master_db.get_n_practice_wins(username)*3 + game_master_db.get_n_practice_draws(username)
            practice_plays_num = game_master_db.get_n_practice_plays(username)
            tournament_score = game_master_db.get_n_tournament_wins(username)*3 + game_master_db.get_n_tournament_draws(username)
            tournament_plays_num = game_master_db.get_n_tournament_plays(username)
            wins = game_master_db.get_n_wins(username)
            draws = game_master_db.get_n_draws(username)
            loses = game_master_db.get_n_loses(username)
            plays = game_master_db.get_all_plays_formated(username)
            response = json.dumps({"practiceScore":practice_score, "practicePlaysNum":practice_plays_num, "tournamentScore":tournament_score, "tournamentPlaysNum": tournament_plays_num, "wins": wins, "ties": draws, "losses": loses, "plays": plays}, indent=4)
            self.send_response(200)
            self.send_header('Content-type','application/json')
            self.end_headers()
            self.wfile.write(response.encode("utf-8"))
        elif self.path.endswith('/createTournament'):
            tournament_name = (json_body['tournamentName'])
            game_type = (json_body['gameType'])
            if game_master_db.check_tournament(tournament_name):
                self.send_response(500)
                self.end_headers()
                return
            game_master_db.insert_tournament(tournament_name, game_type)
            threading.Thread(target=manage_tournament, args=(tournament_name, game_type,)).start()
            self.send_response(200)
            self.end_headers()


def main():
    port = 8080
    server = HTTPServer(('', port ), game_master_handler)
    print('Game Master Server running on port: ' + str(port))
    server.serve_forever()


if __name__ == '__main__':
    main()