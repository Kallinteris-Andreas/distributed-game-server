from http.server import HTTPServer, BaseHTTPRequestHandler
import game_master_db
import json
import requests
import pymongo
import game_master_mongo_interface

practice_chess_waiting_list = None
practice_ttt_waiting_list = None
NO_TOURNAMENT = ''
play_master_url = "http://127.0.0.1:8083/"
auth_url = "http://127.0.0.1:8081/"

class auth_handler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path.endswith('/gameFinished'):
            finished_games = game_master_mongo_interface.extract_finished_games()
            for game_id in finished_games:
                game_master_db.finish_match(game_id)
            self.send_response(200)
            self.end_headers()
        #elif self.path.endswith('/getTournaments'):
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
                    game_master_db.create_match('chess', username, username1, NO_TOURNAMENT)
                    data = {'playId': game_master_db.max_match_id(), 'player1': username, "player2": username1, "gameType": game_type, "tournamentName" : ""}
                    json_data = json.dumps(data, indent = 4)
                    print(json_data)
                    r = requests.post(url = play_master_url + "createPlay", data = json_data)
                    while not r.ok:
                        r = requests.post(url = play_master_url + "createPlay", data = json_data)
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
                    game_master_db.create_match('chess', username, username1, NO_TOURNAMENT)
                    data = {'playId': game_master_db.max_match_id(), 'player1': username, "player2": username1, "gameType": game_type, "tournamentName" : ""}
                    json_data = json.dumps(data, indent = 4)
                    print(json_data)
                    r = requests.post(url = play_master_url + "createPlay", data = json_data)
                    while not r.ok:
                        r = requests.post(url = play_master_url + "createPlay", data = json_data)
                    self.send_response(200)
                    self.end_headers()
            else:
                self.send_response(500)
                self.end_headers()
        elif self.path.endswith('/cancelNewPractice'):
            username = (json_body['username'])
            if practice_chess_waiting_list == username:
                practice_chess_waiting_list = None
            if practice_ttt_waiting_list == username:
                practice_ttt_waiting_list = None
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
            response = json.dumps({"practiceScore":practice_score, "practicePlaysNum":practice_plays_num, "tournamentScore":tournament_score, "tournamentPlaysNum": tournament_plays_num, "wins": wins, "ties": draws, "loses": loses, "plays": plays}, indent=4)
            self.send_response(200)
            self.send_header('Content-type','application/json')
            self.end_headers()
            self.wfile.write(response.encode("utf-8"))
        elif self.path.endswith('/createTournament'):
            tournament_name = (json_body['tournamentName'])
            if game_master_db.check_tournament(tournament_name):
                self.send_response(500)
                self.end_headers()
            r = requests.get(url = auth_url + "listPlayers")
            json_response = json.loads(r.text)
            players_string = ''
            for i in json_response:
                #print(i["username"])
                players_string += i["username"] + '\n'
            players_string = players_string.rstrip()
            print(players_string)

def main():
    port = 6969
    server = HTTPServer(('', port ), auth_handler)
    print('Game Master Server running on port: ' + str(port))
    server.serve_forever()


if __name__ == '__main__':
    main()