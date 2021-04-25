<?php 
if(isset($_GET['token'])){
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
?>
<html>
 <head>
  <title>Home</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
 </head>
 <body onload='getAvailPlays()'>
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
		<input type='button' class='bigbtn' onclick='practiceChess()' value='Practice play chess'/>
		<input type='button' class='bigbtn' onclick='practiceTTT()' value='Practice play tic tac toe'/>
		<p>
		<h2>Available plays:</h2>
		<div id='availPlays'>
		</div>

	</div>

	<script>
		function goToGame(playId,gameType){
			var strVars='?playId='+playId+'&token=<?php echo($_GET["token"]) ?>';
			if(gameType=='chess'){
				window.location.replace("chess.php"+strVars);
			}else if(gameType=='tictactoe'){
				window.location.replace("ttt.php"+strVars);
			}
		}
		function updateAvailPlays(req){
			var res=JSON.parse(req.responseText);
		 	if(res.length==0){
		 		document.getElementById('availPlays').innerHTML='No available plays';
		 		return;
		 	}
		 	var str='<table><tr><th>Game type</th><th>Opponent</th><th>Tournament name/Practice</th><th class="noborder"></th></tr>';
		 	for(var i=0; i<res.length; i++){
		 		str+='<tr><td>';
		 		if(res[i].gameType=='chess'){
		 			str+='Chess';
		 		}else if(res[i].gameType=='tictactoe'){
		 			str+='Tic Tac Toe';
		 		}
		 		str+='</td><td>'+res[i].opponent+'</td><td>';
		 		if(res[i].tournamentName==""){
		 			str+='Practice';
		 		}else{
		 			str+=res[i].tournamentName;
		 		}
		 		str+='</td><td class="noborder"><input type="button" class="bigbtn fill"';
		 		str+=' onclick="goToGame('+res[i].playId+','+"'"+res[i].gameType+"'"+')" value="Play"/></td></tr>';
		 	}
		 	str+='</table>';
		 	document.getElementById('availPlays').innerHTML=str;
		}
		function getAvailPlays(){
			var req=new XMLHttpRequest();
			req.onreadystatechange=function(){
				if(this.readyState==4 && this.status==200){
					updateAvailPlays(this);
				}
			}
			req.open("GET",'funcs/getAvailPlays.php?token=<?php echo($_GET["token"])?>',true);
			req.send();
		}

	</script>
 </body>
</html>






