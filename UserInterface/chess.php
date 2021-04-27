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
$cq=curl_init();
curl_setopt($cq,CURLOPT_URL,'http://playmaster:8080/getPlay');
curl_setopt($cq,CURLOPT_POST,true);
curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
$postValue=json_encode(array('playId'=>(int)$_GET['playId'],'gameType'=>'chess','username'=>$username));
curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
$gameinfo=curl_exec($cq);
if($gameinfo==false||curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
	exit(header("Location: index.php"));
}
curl_close($cq);
$gameinfoarr=json_decode($gameinfo,true);
//player1 is white, player2 is black
$opponent=$gameinfoarr['player1'];
$mycolor='b';
if($gameinfoarr['player1']==$username){
	$opponent=$gameinfoarr['player2'];
	$mycolor='w';
}

?>
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
 			<li><a href='home.php?token=<?php echo($_GET["token"]) ?>'>Home</a></li>
 			<?php 
 				if($role[1]=="1"){
 					echo("<li><a href='official.php?token=".$_GET["token"]."'>Tournaments</a></li>");
 				}
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
		<div><div id='theBoard' class='chessdiv'></div></div>
		<div id='statusdiv'></div>
	</div>
	<script>
	var board = null;
	var game = new Chess('<?php echo($gameinfoarr["gameState"])?>');

	function getPlayState(){
		var req=new XMLHttpRequest();
		req.onreadystatechange=function(){
			if(this.readyState==4){
				if(this.status!=200){
					return;
				}
				game.load(this.responseText);
				board.position(game.fen());
				updateStatus(null);
				if(!game.game_over() && game.turn()!=='<?php echo($mycolor)?>'){
					setTimeout(getPlayState,1000);
				}
				if(game.game_over() && game.turn()==='<?php echo($mycolor)?>'){
					/////////////////////////TODO:inform game master its over
				}
			}
		}
		req.open("POST",'funcs/getPlay.php?token=<?php echo($_GET["token"])?>',true);
		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		req.send('gameType=chess&playId=<?php echo($_GET["playId"])?>');
	}

	function onDragStart (source, piece, position, orientation) {
	  // do not pick up pieces if the game is over
	  if (game.game_over()) return false

	  // only pick up my pieces when its my turn
	  if(!(game.turn()==='<?php echo($mycolor)?>' && piece.search(/^<?php echo($mycolor)?>/) !== -1)){
	  	return false;
	  }
	}

	function onDrop (source, target) {
	  // see if the move is legal
	  var move = game.move({
	    from: source,
	    to: target,
	    promotion: 'q' // NOTE: always promote to a queen for simplicity
	  })

	  // illegal move
	  if (move === null) return 'snapback'

	  updateStatus(move);
	}

	// update the board position after the piece snap
	// for castling, en passant, pawn promotion
	function onSnapEnd () {
	  board.position(game.fen());
	}

	function updateStatus (newmove) {
		var status = document.getElementById('statusdiv');
		if(newmove!=null){
			var req=new XMLHttpRequest();
			req.onreadystatechange=function(){
				if(this.readyState==4){
					getPlayState();
				}
			}
			req.open("POST",'funcs/updatePlay.php?token=<?php echo($_GET["token"])?>',true);
			req.send(JSON.stringify({gameType:'chess',move:newmove,playId:<?php echo($_GET['playId'])?>}));
		}
		if (game.in_checkmate()) {// checkmate?
			if(game.turn() === '<?php echo($mycolor)?>'){
				status.innerHTML = 'Checkmate: <?php echo($opponent)?> won.';
			}else{
				status.innerHTML = 'Checkmate: <?php echo($username)?> won.';
			}
		}else if (game.in_draw()) {// draw?
			status.innerHTML = 'Game over, it\'s a draw';
		}else {// game still on
			if(game.turn() === '<?php echo($mycolor)?>'){
				status.innerHTML = '<?php echo($username)?> to move';
			}else{
				status.innerHTML = '<?php echo($opponent)?> to move';
			}
		}
	}
	<?php 
	$orient='white';
	if($mycolor=='b'){
		$orient='black';
	}?>

	var config = {
	  draggable: true,
	  orientation: '<?php echo($orient)?>',
	  position: '<?php echo($gameinfoarr["gameState"])?>',
	  onDragStart: onDragStart,
	  onDrop: onDrop,
	  onSnapEnd: onSnapEnd
	}
	board = Chessboard('theBoard', config);
	updateStatus(null);
	if(!game.game_over() && game.turn()!=='<?php echo($mycolor)?>'){
		setTimeout(getPlayState,1000);
	}
	</script>
 </body>
</html>






