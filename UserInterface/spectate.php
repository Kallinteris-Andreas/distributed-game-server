<?php 
set_time_limit(0);
if(isset($_GET['token'])&&isset($_GET['playId'])&&isset($_GET['gameType'])){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/validateToken');
	curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('token'=>$_GET['token']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	$response=curl_exec($cq);
	if($response==false || curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
		exit(header("Location: index.php"));
	}else{
		$res=json_decode($response,true);
		$role=$res['role'];
		$username=$res['username'];
	}
	curl_close($cq);
}else{
	exit(header("Location: index.php"));
}
if($role[0]!='1'){
	exit(header("Location: index.php?token=".$_GET['token']));
}
$gameinfo=false;
while($gameinfo===false){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://playmaster:8080/getPlay');
	curl_setopt($cq,CURLOPT_POST,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
	$postValue=json_encode(array('playId'=>(int)$_GET['playId'],'gameType'=>$_GET['gameType']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	$gameinfo=curl_exec($cq);
	if($gameinfo!==false && curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
		exit(header("Location: index.php"));
	}
	curl_close($cq);
}
$gameinfoarr=json_decode($gameinfo,true);
if($_GET['gameType']=='chess'){ ?>
<html>
 <head>
  <title>Chess play</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
  <link rel="stylesheet"
  href="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.css"
  integrity="sha384-q94+BZtLrkL1/ohfjR8c6L+A6qzNH9R2hBLwyoAfu3i/WCvQjzL2RQJ3uNHDISdU"
  crossorigin="anonymous">
 </head>
 <body>
 	<script src="https://code.jquery.com/jquery-3.5.1.min.js"
    integrity="sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2"
    crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.2/chess.js" integrity="sha384-s3XgLpvmHyscVpijnseAmye819Ee3yaGa8NxstkJVyA6nuDFjt59u1QvuEl/mecz" crossorigin="anonymous"></script>

	<script src="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.js"
    integrity="sha384-8Vi8VHwn3vjQ9eUHUxex3JSN/NFqUg3QbPyX8kWyb93+8AC/pPWTzj+nHtbC5bxD"
    crossorigin="anonymous"></script>

 	<div class='menubar'>
 		<ul>
 			<li><a href='profile.php?token=<?php echo($_GET["token"]) ?>'>My profile</a></li>
 			<li><a href='play.php?token=<?php echo($_GET["token"]) ?>'>Play</a></li>
 			<li><a href='tournaments.php?token=<?php echo($_GET["token"]) ?>'>Tournaments</a></li>
 			<li><a href='allPlayers.php?token=<?php echo($_GET["token"]) ?>'>View all player scores</a></li>
 			<?php 
 				if($role[2]=="1"){
 					echo("<li><a href='admin.php?token=".$_GET["token"]."'>Administration</a></li>");
 				}
 			?>
 			<li><a style='float:right' href='index.php?token=<?php echo($_GET["token"]) ?>'>Log out</a></li>
 		</ul>
	</div>
	
	<div class='mainpage'>
		<span style='float:right'>Logged in as <i> <?php echo($username)?> </i></span><p><p>
		Spectating: <?php echo($gameinfoarr['player1'].' - '.$gameinfoarr['player2']) ?> <p>
		<div><div id='theBoard' class='chessdiv'></div></div>
		<br><br>
		<div id='statusdiv' style='text-align: center;'></div>
	</div>
	<script>
	var board = null;
	var game = new Chess('<?php echo($gameinfoarr["gameState"])?>');

	function getPlayState(){
		var req=new XMLHttpRequest();
		req.onreadystatechange=function(){
			if(this.readyState==4){
				if(this.status!=200){
					if(this.status!=500 && this.status!=404){
						setTimeout(getPlayState,700);
					}
					return;
				}
				game.load(this.responseText);
				board.position(game.fen());
				updateStatus();
				if(!game.game_over()){
					setTimeout(getPlayState,700);
				}
			}
		}
		req.open("POST",'funcs/getPlay.php?token=<?php echo($_GET["token"])?>',true);
		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		req.send('gameType=chess&playId=<?php echo($_GET["playId"])?>');
	}	
	function updateStatus () {
		var status = document.getElementById('statusdiv');
		if (game.in_checkmate()) {// checkmate?
			if(game.turn() === 'w'){
				status.innerHTML = 'Checkmate: <?php echo($gameinfoarr["player2"])?> won.';
			}else{
				status.innerHTML = 'Checkmate: <?php echo($gameinfoarr["player1"])?> won.';
			}
		}else if (game.in_draw()) {// draw?
			status.innerHTML = 'Game over, it\'s a draw';
		}else {// game still on
			if(game.turn() === 'w'){
				status.innerHTML = '<?php echo($gameinfoarr["player1"])?> (white) to move';
			}else{
				status.innerHTML = '<?php echo($gameinfoarr["player2"])?> (black) to move';
			}
		}
	}
	var config = {
	  position: '<?php echo($gameinfoarr["gameState"])?>',
	}
	board = Chessboard('theBoard', config);
	updateStatus();
	if(!game.game_over()){
		setTimeout(getPlayState,700);
	}
	</script>
 </body>
</html>

<?php } else { ?>
<html>
 <head>
  <title>Tictactoe play</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
 </head>
 <body>
 	<div class='menubar'>
 		<ul>
 			<li><a href='profile.php?token=<?php echo($_GET["token"]) ?>'>My profile</a></li>
 			<li><a href='play.php?token=<?php echo($_GET["token"]) ?>'>Play</a></li>
 			<li><a href='tournaments.php?token=<?php echo($_GET["token"]) ?>'>Tournaments</a></li>
 			<li><a href='allPlayers.php?token=<?php echo($_GET["token"]) ?>'>View all player scores</a></li>
 			<?php 
 				if($role[2]=="1"){
 					echo("<li><a href='admin.php?token=".$_GET["token"]."'>Administration</a></li>");
 				}
 			?>
 			<li><a style='float:right' href='index.php?token=<?php echo($_GET["token"]) ?>'>Log out</a></li>
 		</ul>
	</div>
	
	<div class='mainpage'>
		<span style='float:right'>Logged in as <i> <?php echo($username)?> </i></span><p><p>
		Spectating: <?php echo($gameinfoarr['player1'].' - '.$gameinfoarr['player2']) ?> <p>
		<section style='text-align: center;'>
		<div class="game--container">
            <div id="cell0" data-cell-index="0" class="cell"></div>
            <div id="cell1" data-cell-index="1" class="cell"></div>
            <div id="cell2" data-cell-index="2" class="cell"></div>
            <div id="cell3" data-cell-index="3" class="cell"></div>
            <div id="cell4" data-cell-index="4" class="cell"></div>
            <div id="cell5" data-cell-index="5" class="cell"></div>
            <div id="cell6" data-cell-index="6" class="cell"></div>
            <div id="cell7" data-cell-index="7" class="cell"></div>
            <div id="cell8" data-cell-index="8" class="cell"></div>
        </div>
		<div id='statusdiv'></div>
		</section>
	</div>
	<script>
	const statusDisplay = document.getElementById('statusdiv');
	var gameState = '<?php echo($gameinfoarr["gameState"])?>'.split('');
	var board=Array(9);
	for(let j=0; j<=8; j++){
		board[j]=document.getElementById('cell'+j);
	}
	const winningConditions = [
	    [0, 1, 2],
	    [3, 4, 5],
	    [6, 7, 8],
	    [0, 3, 6],
	    [1, 4, 7],
	    [2, 5, 8],
	    [0, 4, 8],
	    [2, 4, 6]
	];
	if(game_over()=='N'){
		setTimeout(getPlayState,1000);
	}
	updateBoard();

	function updateBoard(){
		for(let j=0; j<=8; j++){
			if(gameState[j]!='N'){
				board[j].innerHTML=gameState[j];
			}
		}
		let res=game_over();
		if(res=='N'){
			if(gameState[9] == 'X'){
				statusDisplay.innerHTML = '<?php echo($gameinfoarr['player1'])?> (X) plays';
			}else{
				statusDisplay.innerHTML = '<?php echo($gameinfoarr['player2'])?> (O) plays';
			}
		}else if(res=='D'){
			statusDisplay.innerHTML='Draw';
		}else if(res=='X'){
			statusDisplay.innerHTML = '<?php echo($gameinfoarr['player1'])?> won.';
		}else{
			statusDisplay.innerHTML = '<?php echo($gameinfoarr['player2'])?> won.';
		}
	}

	function game_over(){
		//return X,O if won, D for draw, N for not over
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

	function getPlayState(){
		var req=new XMLHttpRequest();
		req.onreadystatechange=function(){
			if(this.readyState==4){
				if(this.status!=200){
					return;
				}
				gameState=this.responseText.split('');
				updateBoard();
				var finished=game_over();
				if(finished=='N'){
					setTimeout(getPlayState,700);
				}
			}
		}
		req.open("POST",'funcs/getPlay.php?token=<?php echo($_GET["token"])?>',true);
		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		req.send('gameType=tictactoe&playId=<?php echo($_GET["playId"])?>');
	}
	</script>
 </body>
</html>

<?php } ?>






