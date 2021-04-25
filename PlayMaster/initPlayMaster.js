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
			  		throw err;
			  	}
			  	var dbPlays = db.db("Plays");
			  	var finResult=[];
			  	var finCounter=0;
			 	dbPlays.collection("chessPlays").find({$and:[{$or:[{player1:postData.username},{player2:postData.username}]},{isFinished:"F"}]},{projection:{_id:0,gameState:0,isFinished:0}}).toArray(function(err,result){
			 		if(err!=null){
			 			throw err;
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
				dbPlays.collection("tttPlays").find({$and:[{$or:[{player1:postData.username},{player2:postData.username}]},{isFinished:"F"}]},{projection:{_id:0,gameState:0,isFinished:0}}).toArray(function(err,result){
			 		if(err!=null){
			 			throw err;
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
			  		throw err;
			  	}
			  	var dbPlays = db.db("Plays");
			  	var collName;
			  	if(postData.gameType=='chess'){
			  		collName='chessPlays';
			  	}else{
			  		collName='tttPlays';
			  	}
			  	dbPlays.collection(collName).findOne({playId:postData.playId},{projection:{_id:0,playId:0}},function(err,result){
			  		if(err!=null){
			  			throw err;
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
			  		throw err;
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
			  			throw err;
			  		}
			  		if(result.player1!=postData.username && result.player2!=postData.username && result.isFinished=="F"){
			  			res.writeHead(500);
			  			res.end('');
			  			db.close();
			  			return;
			  		}
			  		var newState;
			  		var newFinished="F";
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
						}
			  		}else if(collName=='tttPlays'){
			  			//...
			  		}
					dbPlays.collection(collName).updateOne({playId:postData.playId},{$set:{gameState:newState,isFinished:newFinished}},function(err,result){
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
	}else{
		res.writeHead(404, {'Content-Type': 'text/html'});
		res.end('<h1>Url not found</h1>');
	}
}).listen(8080);