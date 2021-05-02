from pymongo import MongoClient
URL = "mongodb://mongo:27017"
client = MongoClient(URL)
db = client.Plays

def extract_finished_games():
    finished_games = [] #is a list of tuples indicating match_id and match_winner and match_loser
    n = True
    while n != None:
        #extract one fisnished play from either 'chessPlays' or 'tttPLays' collections
        n  = db.get_collection('chessPlays').find_one_and_delete({'isFinished':"T"})
        if n == None:
            n  = db.get_collection('tttPlays').find_one_and_delete({'isFinished':"T"})


        if n != None:
            print("winner is:" + str(n['winner']), flush=True)
            if n['winner']: #if there is a winner
                if n['player1'] == n['winner']:
                    loser = n['player2']
                elif n['player2'] == n['winner']:
                    loser = n['player1']
                finished_games.append([n['playId'], n['winner'], loser])
            else:
                print("bbaabksahb")
                finished_games.append([n['playId'], n['winner'], n['player1'], n['player2']])
    return finished_games

def main():
    pass
    print(extract_finished_games())

if __name__ == '__main__':
    main()