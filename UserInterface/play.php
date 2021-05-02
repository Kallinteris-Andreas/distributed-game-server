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
if($role[0]!='1'){
	exit(header("Location: index.php?token=".$_GET['token']));
}
?>
<html>
 <head>
  <title>Play</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
 </head>
 <body onload='getAvailPlays()'>
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
		<span style='float:right'>Logged in as <i> <?php echo($username)?> </i></span><br><br>
		<input id='btnC' type='button' class='bigbtn' onclick='practicePlay("chess")' value='Practice play chess'/>
		<input id='btnT' type='button' class='bigbtn' onclick='practicePlay("tictactoe")' value='Practice play tic tac toe'/>
		<span id='txtLoading' style='display:none; white-space:pre'>Searching for second player...   </span>
		<input id='btnCancel' type='button' class='bigbtn' style='display:none' onclick='cancelPracticeSearch()' value='Cancel'/>
		<p>
		<h2>Available plays:</h2>
		<div id='availPlays'>
		</div>

	</div>

	<script>
		var req=null;
		var animActive=false;
		var gameTypeReq=null;
		function practicePlay(gameType){
			var link='chess';
			if(gameType=='tictactoe'){
				link='ttt';
			}
			document.getElementById('btnC').style.display='none';
			document.getElementById('btnT').style.display='none';
			document.getElementById('txtLoading').style.display='initial';
			document.getElementById('btnCancel').style.display='initial';
			req=new XMLHttpRequest();
			req.onreadystatechange=function(){
				if(this.readyState==4 && this.status==200){
					var newPlayId=JSON.parse(this.responseText).playId;
					window.location.replace(link+'.php?playId='+newPlayId+'&token=<?php echo($_GET["token"])?>');
				}
			}
			req.open("POST",'funcs/reqPractice.php?token=<?php echo($_GET["token"])?>',true);
			req.setRequestHeader("Content-type", "application/json");
			req.send('{"gameType":"'+gameType+'"}');
			gameTypeReq=gameType;
			if(!animActive){
				setTimeout(loading,1000);
			}
			animActive=true;
		}
		function loading(){
			let txt=document.getElementById('txtLoading');
			if(txt.style.display=='none'){
				animActive=false;
				return;
			}
			if(txt.innerHTML.endsWith("...")){
				txt.innerHTML='Searching for second player...   ';
			}else{
				txt.innerHTML='Searching for second player......';
			}
			setTimeout(loading,1000);
		}
		function cancelPracticeSearch(){
			req.abort();
			document.getElementById('btnCancel').style.display='none';
			document.getElementById('txtLoading').style.display='none';
			req=new XMLHttpRequest();
			req.onreadystatechange=function(){
				if(this.readyState==4){
					document.getElementById('btnC').style.display='initial';
					document.getElementById('btnT').style.display='initial';
				}
			};
			req.open("GET",'funcs/cancelPractice.php?gameType='+gameTypeReq+'&token=<?php echo($_GET["token"])?>',true);
			req.send();			
		}
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






