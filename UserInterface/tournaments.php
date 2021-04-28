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
$cq=curl_init();
curl_setopt($cq,CURLOPT_URL,'http://gamemaster:8080/getTournaments');
curl_setopt($cq,CURLOPT_RETURNTRANSFER,true);
$res=json_decode(curl_exec($cq),true);
curl_close($cq);
?>
<html>
 <head>
  <title>Tournaments</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
 </head>
 <body>
 	<div class='menubar'>
 		<ul>
 			<li><a href='profile.php?token=<?php echo($_GET["token"]) ?>'>My profile</a></li>
 			<?php 
 				if($role[0]=="1"){
 					echo("<li><a href='play.php?token=".$_GET["token"]."'>Play</a></li>");
 				}
 			?>
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
		<div>
<?php 
if($role[1]=="1"){
	echo("<input type='button' class='bigbtn' onclick='createTourn()' value='Create new tournament'/><br><br>");
}
if(count($res)==0){
	echo('No tournaments yet');
}else{
	echo('<table>');
	for($i=0;$i<count($res);$i++){
		echo('<tr><td><h4>'.$res[$i]['tournamentName'].'</h4></td>');
		echo('<td>1st place: '.$res[$i]['place1'].'<br>2nd place: '.$res[$i]['place2'].'<br>3rd place: '.$res[$i]['place3'].'<br>4th place: '.$res[$i]['place4']);
		echo('</td></tr><tr><td class=noborder >');
		$playarr=$res[$i]['plays'];
		if(count($playarr)==0){
			echo('No plays yet</td></tr>');
		}else{
			for($j=0;$j<count($playarr);$j++){
				echo($playarr[$j]['player1'].' - '.$playarr[$j]['player2'].' : '.$playarr[$j]['score'].'<br>');
			}
			echo('</td></tr>');
		}		
	}
	echo('</table>');
}?>
		</div>
	</div>

<?php if($role[1]=="1"){ ?>
	<script>
		function createTourn(){
			var name=prompt('Enter name of tournament:');
			if(name==null){
				return;
			}
			req=new XMLHttpRequest();
			req.onreadystatechange=function(){
				if(this.readyState==4){
					if(this.status!=200){
						alert('Tournament could not be created: there is already a tournament with that name');
					}else{
						window.location.replace('tournaments.php?token=<?php echo($_GET["token"])?>');
					}
				}
			}
			req.open("POST",'funcs/createTournament.php?token=<?php echo($_GET["token"])?>',true);
			req.setRequestHeader("Content-type", "application/json");
			req.send('{"tournamentName":"'+name+'"}');
		}
	</script>
<?php } ?>

</body>
</html>
