if(!db.getMongo().getDBNames().includes("Plays")){
	db=db.getSiblingDB('Plays');
	db.createCollection('chessPlays');
	db.chessPlays.insert({'playId':950,"tournamentName":"World Cup","player1":"Andy","player2":"user1","gameState":"rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1","isFinished":"F","winner":""});
	db.chessPlays.insert({'playId':951,"tournamentName":"","player1":"user2","player2":"Andy","gameState":"8/8/8/4b3/8/8/1q6/1K6 w - - 0 1","isFinished":"T","winner":"Andy"});
	db.createCollection('tttPlays');
	db.tttPlays.insert({'playId':952,"tournamentName":"Europe Cup","player1":"Andy","player2":"user1","gameState":"NNNNNNNNNX","isFinished":"F","winner":""});
	db.tttPlays.insert({'playId':953,"tournamentName":"","player1":"user1","player2":"user2","gameState":"XXXNOONNNO","isFinished":"T","winner":"user1"});
}