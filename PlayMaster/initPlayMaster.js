var http = require('http');
var MongoClient = require('mongodb').MongoClient;
var url = "mongodb://mongo:27017/";
http.createServer( function (req,res){
	if(req.url=="/availPlays"){
		var postDataJSON='';
		req.on('data', function (data) {
            postDataJSON += data;
        });
        req.on('end', function () {
            var postData = JSON.parse(postDataJSON);
            MongoClient.connect(url, function(err, db) {
			  	if (err!=null){
			  		res.writeHead(500);
			  		res.end('');
			  	}
			  	var dbPlays = db.db("Plays");
			  	var finResult=[];
			  	var finCounter=0;
			 	dbPlays.collection("chessPlays").find({$and:[{$or:[{player1:postData.username},{player2:postData.username}]},{isFinished:"F"}]},{projection:{_id:0,gameState:0,isFinished:0,winner:0}}).toArray(function(err,result){
			 		if(err!=null){
			 			res.writeHead(500);
			  			res.end('');
			 		}
			 		result.forEach(function(counter){
			 			var opp;
			 			if(counter.player1==postData.username){
			 				opp=counter.player2;
			 			}else{
			 				opp=counter.player1;
			 			}
			 			finResult.push({playId:counter.playId,tournamentName:counter.tournamentName,opponent:opp,gameType:"chess"});
			 		});
			 		finCounter++;
			 		if(finCounter==2){
			 			res.writeHead(200, {'Content-Type': 'application/json'});
						res.end(JSON.stringify(finResult));
						db.close();
			 		}
				});
				dbPlays.collection("tttPlays").find({$and:[{$or:[{player1:postData.username},{player2:postData.username}]},{isFinished:"F"}]},{projection:{_id:0,gameState:0,isFinished:0,winner:0}}).toArray(function(err,result){
			 		if(err!=null){
			 			res.writeHead(500);
			  			res.end('');
			 		}
			 		result.forEach(function(counter){
			 			var opp;
			 			if(counter.player1==postData.username){
			 				opp=counter.player2;
			 			}else{
			 				opp=counter.player1;
			 			}
			 			finResult.push({playId:counter.playId,tournamentName:counter.tournamentName,opponent:opp,gameType:"tictactoe"});
			 		});
			 		finCounter++;
			 		if(finCounter==2){
			 			res.writeHead(200, {'Content-Type': 'application/json'});
						res.end(JSON.stringify(finResult));
						db.close();
			 		}
			 	});
			});
        });
	}else if(req.url=="/getPlay"){
		var postDataJSON='';
		req.on('data', function (data) {
            postDataJSON += data;
        });
        req.on('end', function () {
            var postData = JSON.parse(postDataJSON);
            MongoClient.connect(url, function(err, db) {
			  	if (err!=null){
			  		res.writeHead(500);
			  		res.end('');
			  	}
			  	var dbPlays = db.db("Plays");
			  	var collName;
			  	if(postData.gameType=='chess'){
			  		collName='chessPlays';
			  	}else{
			  		collName='tttPlays';
			  	}
			  	dbPlays.collection(collName).findOne({playId:postData.playId},{projection:{_id:0,playId:0,winner:0}},function(err,result){
			  		if(err!=null){
			  			res.writeHead(500);
			  			res.end('');
			  		}
			  		if(result==null||(result.player1!=postData.username&&result.player2!=postData.username)){
			  			res.writeHead(404);
			  			res.end('');
			  		}else{
			  			res.writeHead(200, {'Content-Type': 'application/json'});
			  			res.end(JSON.stringify(result));
			  		}					
					db.close();
			  	});
			});
        });
	}else if(req.url=="/updatePlay"){
		var postDataJSON='';
		req.on('data', function (data) {
            postDataJSON += data;
        });
        req.on('end', function () {
            var postData = JSON.parse(postDataJSON);
            MongoClient.connect(url, function(err, db) {
			  	if (err!=null){
			  		res.writeHead(500);
			  		res.end('');
			  	}
			  	var dbPlays = db.db("Plays");
			  	var collName;
			  	if(postData.gameType=='chess'){
			  		collName='chessPlays';
			  	}else{
			  		collName='tttPlays';
			  	}
			  	dbPlays.collection(collName).findOne({playId:postData.playId},{projection:{_id:0,playId:0,tournamentName:0}},function(err,result){
			  		if(err!=null){
			  			res.writeHead(500);
			  			res.end('');
			  		}
			  		if((result.player1!=postData.username && result.player2!=postData.username)|| result.isFinished=="T"){
			  			res.writeHead(500);
			  			res.end('');
			  			db.close();
			  			return;
			  		}
			  		var newState;
			  		var newFinished="F";
			  		var newWinner="";
			  		if(collName=='chessPlays'){
			  			const { Chess } = require('chess.js');
						var chess = new Chess(result.gameState);
						if(chess.move(postData.move)==null){
							res.writeHead(500);
				  			res.end('');
				  			db.close();
				  			return;
						}
						newState=chess.fen();
						if(chess.game_over()){
							newFinished="T";
							if(chess.in_checkmate()){
								if(chess.turn()=='w'){
									newWinner=result.player2;
								}else{
									newWinner=result.player1;
								}
							}
						}
			  		}else if(collName=='tttPlays'){
			  			newStateArr=result.gameState.split('');
			  			if(!((newStateArr[9]=='X' && result.player1==postData.username)||(newStateArr[9]=='O' && result.player2==postData.username))){
			  				res.writeHead(500);
				  			res.end('');
				  			db.close();
				  			return;
			  			}

			  			if(newStateArr[postData.move]!='N'){
			  				res.writeHead(500);
				  			res.end('');
				  			db.close();
				  			return;
			  			}
			  			newStateArr[postData.move]=newStateArr[9];
			  			if(newStateArr[9]=='X'){
			  				newStateArr[9]='O';
			  			}else{
			  				newStateArr[9]='X';
			  			}
			  			var tmp=ttt_game_over(newStateArr)
			  			if(tmp!='N'){
			  				newFinished='T';
			  				if(tmp=='X'){
			  					newWinner=result.player1;
			  				}else if(tmp=='O'){
			  					newWinner=result.player2;
			  				}
			  			}
			  			newState=newStateArr.toString().replaceAll(",","");
			  		}
					dbPlays.collection(collName).updateOne({playId:postData.playId},{$set:{gameState:newState,isFinished:newFinished,winner:newWinner}},function(err,result){
						if(err!=null || result.modifiedCount!=1){
							res.writeHead(500);
				  			res.end('');
				  			db.close();
				  			return;
						}
						res.writeHead(200);
						res.end('');
						db.close;
					})
			  	});
			});
        });
    }else if(req.url=="/createPlay"){
		var postDataJSON='';
		req.on('data', function (data) {
            postDataJSON += data;
        });
        req.on('end', function () {
            var postData = JSON.parse(postDataJSON);
            if(postData.playId==null){
            	res.writeHead(500);
            	res.end('');
            	db.close();
            	return;
            }
            MongoClient.connect(url, function(err, db) {
			  	if (err!=null){
			  		res.writeHead(500);
			  		res.end('');
			  	}
			  	var dbPlays = db.db("Plays");
			  	var collName;
			  	var initGameState;
			  	if(postData.gameType=='chess'){
			  		collName='chessPlays';
			  		initGameState='rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
			  	}else{
			  		collName='tttPlays';
			  		initGameState='NNNNNNNNNX';
			  	}
			  	if(Math.floor(Math.random() * 2) == 0){
			  		var tmp=postData.player1;
			  		postData.player1=postData.player2;
			  		postData.player2=tmp;
			  	}
			  	dbPlays.collection(collName).find({playId:postData.playId}).count(function(err,num){
			  		if(err!=null){
			  			res.writeHead(500);
			  			res.end('');
			  		}
			  		if(num>0){
			  			res.writeHead(200);
			  			res.end('');
			  			db.close();
			  			return;
			  		}
			  		dbPlays.collection(collName).insertOne({playId:postData.playId,tournamentName:postData.tournamentName,player1:postData.player1,player2:postData.player2,gameState:initGameState,isFinished:"F",winner:""},function(err,insRes){
						if(err!=null){
				  			res.writeHead(500);
			  				res.end('');
						}
			  			res.writeHead(200);
			  			res.end('');
			  			db.close();
			  		});
			  	});
			});
        });
	}else{
		res.writeHead(404, {'Content-Type': 'text/html'});
		res.end('<h1>Url not found</h1>');
	}
}).listen(8080);




function ttt_game_over(gameState){
	//return X,O if won, D for draw, N for not over
	let winningConditions = [
	    [0, 1, 2],
	    [3, 4, 5],
	    [6, 7, 8],
	    [0, 3, 6],
	    [1, 4, 7],
	    [2, 5, 8],
	    [0, 4, 8],
	    [2, 4, 6]
	];
	for (let i = 0; i <= 7; i++) {
        const winCondition = winningConditions[i];
        let a = gameState[winCondition[0]];
        let b = gameState[winCondition[1]];
        let c = gameState[winCondition[2]];
        if (a === 'N' || b === 'N' || c === 'N') {
            continue;
        }
        if (a === b && b === c ) {
            return a;
        }
    }
    if(!gameState.includes('N')){
    	return 'D';
    }
    return 'N';
}