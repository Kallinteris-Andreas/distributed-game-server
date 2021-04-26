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
 <body>
 	<div class='menubar'>
 		<ul>
 			<li><a href='home.php?token=<?php echo($_GET["token"]) ?>'>Home</a></li>
 			<li><a href='profile.php?token=<?php echo($_GET["token"]) ?>'>My profile</a></li>
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
		Username: <?php echo($username)?> <input type='button' class='bigbtn' onclick='changePsw()' value='Change password...'/>
		<p>

	</div>

	<script>
		function changePsw(){
			var oldpsw=prompt('Enter old password:');
			if(oldpsw==null){
				return;
			}
			var req=new XMLHttpRequest();
			req.onreadystatechange=function(){
				if(this.readyState==4 && this.status==200){
					if(this.responseText=='ERROR'){
						alert('Wrong password');
					}else{
						var newtoken=this.responseText;
						var newpsw=prompt('Enter new password:');
						if(newpsw==null){
							alert('Password not changed');
							return;
						}
						var req2=new XMLHttpRequest();
						req2.onreadystatechange=function(){
							if(this.readyState==4 && this.status==200){
								alert('Password changed! You need to log in again');
								window.location.replace("index.php?token="+newtoken);
							}
						}
						req2.open("POST",'funcs/changePassword.php',true);
						req2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						req2.send('token='+newtoken+'&password='+newpsw);
						newpsw='';
					}
				}
			}
			req.open("POST",'funcs/login.php',true);
			req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			req.send('username=<?php echo($username)?>&password='+oldpsw);
			oldpsw='';
		}
	</script>
 </body>
</html>






