<?php 
if(isset($_GET['token'])&&isset($_GET['playId'])){
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
$cq=curl_init();
curl_setopt($cq,CURLOPT_URL,'http://playmaster:8080/getPlay');
curl_setopt($cq,CURLOPT_POST,true);
curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
$postValue=json_encode(array('playId'=>(int)$_GET['playId'],'gameType'=>'tictactoe'));
curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
$gameinfo=curl_exec($cq);
if($gameinfo==false||curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
	exit(header("Location: index.php"));
}
curl_close($cq);
$gameinfoarr=json_decode($gameinfo,true);
//player1 is X, player2 is O
$opponent=$gameinfoarr['player1'];
$myletter='O';
if($gameinfoarr['player1']==$username){
	$opponent=$gameinfoarr['player2'];
	$myletter='X';
}

?>
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
		Playing against <?php echo($opponent)?><p>
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
	document.querySelectorAll('.cell').forEach(cell => cell.addEventListener('click', handleCellClick));
	if(game_over()=='N' && gameState[9]!='<?php echo($myletter)?>'){
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
			if(gameState[9] == '<?php echo($myletter)?>'){
				statusDisplay.innerHTML = '<?php echo($username)?> ('+gameState[9]+') plays';
			}else{
				statusDisplay.innerHTML = '<?php echo($opponent)?> ('+gameState[9]+') plays';
			}
		}else if(res=='D'){
			statusDisplay.innerHTML='Draw';
		}else if(res=='<?php echo($myletter)?>'){
			statusDisplay.innerHTML = '<?php echo($username)?> won.';
		}else{
			statusDisplay.innerHTML = '<?php echo($opponent)?> won.';
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

	function handleCellClick(clickedCellEvent) {
	    const clickedCell = clickedCellEvent.target;
	    const clickedCellIndex = parseInt(clickedCell.getAttribute('data-cell-index'));

	    if (gameState[clickedCellIndex] != "N" || gameState[9]!='<?php echo($myletter)?>' || game_over(gameState)!='N') {
	        return;
	    }
	    gameState[clickedCellIndex] = '<?php echo($myletter)?>';
	    gameState[9]="<?php if($myletter=='X'){echo('O');}else{echo('X');}?>";
	    updateBoard();
	    var req=new XMLHttpRequest();
		req.onreadystatechange=function(){
			if(this.readyState==4){
				getPlayState();
			}
		}
		req.open("POST",'funcs/updatePlay.php?token=<?php echo($_GET["token"])?>',true);
		req.send(JSON.stringify({gameType:'tictactoe',move:clickedCellIndex,playId:<?php echo($_GET['playId'])?>}));
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
				if(finished=='N' && gameState[9]!='<?php echo($myletter)?>'){
					setTimeout(getPlayState,700);
				}
				if(finished!='N' && gameState[9]=='<?php echo($myletter)?>'){
					var ping=new XMLHttpRequest();
					ping.open("GET",'funcs/gameFinished.php',true);
					ping.send();
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






