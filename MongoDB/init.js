if(!db.getMongo().getDBNames().includes("Plays")){
	db=db.getSiblingDB('Plays');
	db.createCollection('chessPlays');
	db.createCollection('tttPlays');
}
