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
if($role[2]!='1'){
	exit(header("Location: index.php?token=".$_GET['token']));
}
?>
<html>
 <head>
  <title>Administration</title>
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
 			<li><a href='tournaments.php?token=<?php echo($_GET["token"])?>'>Tournaments</a></li>
 			<li><a href='allPlayers.php?token=<?php echo($_GET["token"])?>'>View all player scores</a></li>
 			<li><a href='admin.php?token=<?php echo($_GET["token"])?>'>Administration</a></li>
 			<li><a style='float:right' href='index.php?token=<?php echo($_GET["token"]) ?>'>Log out</a></li>
 		</ul>
	</div>
	<div class='mainpage'>
		<span style='float:right'>Logged in as <i> <?php echo($username)?> </i></span><br><br>
		<div>
			<table id="usertable"></table>
		</div>
	</div>
	<script>
		function refreshUsers(){
			var req=new XMLHttpRequest();
			req.onreadystatechange=function(){
				if(this.readyState==4 && this.status==200){
				 	var res=JSON.parse(req.responseText);
				 	var str='<tr><th>Username</th><th>Roles</th><th class="noborder"></th></tr>';
				 	for(var i=0; i<res.length; i++){
				 		str+='<tr><td id="usr'+i+'">'+res[i].username+'</td><td id="role'+i+'">';
				 		if(res[i].role[0]=="1"){
				 			str+='Player<br>';
				 		}
				 		if(res[i].role[1]=="1"){
				 			str+='Official<br>';
				 		}
				 		if(res[i].role[2]=="1"){
				 			str+='Admin';
				 		}
				 		str+='</td><td class="noborder"><input type="button" class="bigbtn fill" id="btn'+i+'" ';
				 		str+='onclick="editUpdateRole('+i+','+"'"+res[i].role+"'"+')" value="Change roles"></td></tr>';
				 	}
				 	document.getElementById('usertable').innerHTML=str;
				}
			}
			req.open("GET",'funcs/listUsers.php?token=<?php echo($_GET["token"])?>',true);
			req.send();
		}

		function editUpdateRole (num,oldrole) { 
			var btn=document.getElementById('btn'+num);
			if(btn.value=='Change roles'){
				btn.value='Save';
				var btnlist=document.getElementsByClassName('bigbtn');
				for(var i=0; i<btnlist.length; i++){
					if(btnlist[i].id!=btn.id){
						btnlist[i].disabled=true;
					}
				}
				var str='<input type="checkbox" id="playerRole"><label for="playerRole"> Player</label><br>';
				str+='<input type="checkbox" id="officialRole"><label for="officialRole"> Official</label><br>';
				str+='<input type="checkbox" id="adminRole"><label for="adminRole"> Admin</label>';
				document.getElementById('role'+num).innerHTML=str;
				if(oldrole[0]=="1"){
					document.getElementById('playerRole').checked=true;
				}
				if(oldrole[1]=="1"){
					document.getElementById('officialRole').checked=true;
				}
				if(oldrole[2]=="1"){
					document.getElementById('adminRole').checked=true;
				}
			}else{
				btn.disabled=true;
				var i="0";
				var j="0";
				var k="0";
				if(document.getElementById('playerRole').checked==true){
					i="1";
				}
				if(document.getElementById('officialRole').checked==true){
					j="1";
				}
				if(document.getElementById('adminRole').checked==true){
					k="1";
				}
				var newrole=i+j+k;
				var username=document.getElementById('usr'+num).innerHTML;
				var req=new XMLHttpRequest();
				req.onreadystatechange=function(){
					if(this.readyState==4){
						refreshUsers();
					}
				}
				req.open("POST",'funcs/updateRole.php?token=<?php echo($_GET["token"])?>',true);
				req.setRequestHeader("Content-type", "application/json");
				req.send(JSON.stringify({username:username,newrole:newrole}));
			}
		}
		refreshUsers();
	</script>
 </body>
</html>