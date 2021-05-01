from pymongo import MongoClient
URL = "mongodb://mongo:27017"
client = MongoClient(URL)
db = client.Plays

def extract_finished_games():
    finished_games = [] #is a list of tuples indicating match_id and match_winner
    n = True
    while n != None:
        n  = db.get_collection('chessPlays').find_one_and_delete({'isFinished':"T"})
        if n == None:
            n  = db.get_collection('tttPlays').find_one_and_delete({'isFinished':"T"})
        if n != None:
            finished_games.append([n['playId'], n['winner']])
    return finished_games

def main():
    pass
    print(extract_finished_games())

if __name__ == '__main__':
    main()