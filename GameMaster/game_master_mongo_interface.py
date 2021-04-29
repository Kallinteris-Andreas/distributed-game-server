from pymongo import MongoClient
URL = "mongodb://127.0.0.1:8082"
client = MongoClient(URL)
db = client.Plays

def extract_finished_games():
    finished_IDs = []
    n = True
    while n != None:
        n  = db.get_collection('chessPlays').find_one_and_delete({'isFinished':"T"})
        if n == None:
            n  = db.get_collection('tttPlays').find_one_and_delete({'isFinished':"T"})
        if n != None:
            finished_IDs.append(n['playId'])
    return finished_IDs

def main():
    pass
    print(extract_finished_games())

if __name__ == '__main__':
    main()