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
curl_setopt($cq,CURLOPT_URL,'http://gamemaster:8080/getAllPlayers');
curl_setopt($cq,CURLOPT_RETURNTRANSFER,true);
$res=json_decode(curl_exec($cq),true);
curl_close($cq);
?>
<html>
 <head>
  <title>All player scores</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
 </head>
 <body>
 	<div class='menubar'>
 		<ul>
 			<li><a href='home.php?token=<?php echo($_GET["token"]) ?>'>Home</a></li>
 			<li><a href='profile.php?token=<?php echo($_GET["token"]) ?>'>My profile</a></li>
 			<li><a href='tournaments.php?token=<?php echo($_GET["token"]) ?>'>View tournaments</a></li>
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
			<table><tr><th>Username</th><th>Practice plays score</th><th>Tournament plays score</th></tr>
<?php
	for($i=0;$i<count($res);$i++){
		echo('<tr><td>'.$res[$i]['username'].'</td><td>'.$res[$i]['practiceScore'].'</td><td>'.$res[$i]['tournamentScore'].'</td></tr>');
	}
?>
			</table>
		</div>
	</div>
</body>
</html>
