<?php 
if (isset($_GET['token'])){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/logout');
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('token'=>$_GET['token']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
	$response=curl_exec($cq);
	curl_close($cq);
}?>

<html>
 <head>
  <title>Log In</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
 </head>
 <body>
 <div class='mainpage loginmain'>
	<h1 style='text-align:center'>Log in</h1>
	Username: <input type="text" id="txtusername" name="username"> <p> 
	Password: <input type="password" id="txtpsw" name="password"> <p> 
	<input class='bigbtn' id="btnlogin" type="button" onclick='check_login()' value="Log in"/>
	<span style="color:red" id="errorspace"></span><p>
 <div>
 <h2 style='text-align:center'>Don't have an account yet?</h2> <p>
 <input class='bigbtn centbtn' type="button" value="Sign Up" onclick='window.location.href="signup.php"'>
</div>
 </div>
 <script>
 	function check_login(){
 		var username=document.getElementById('txtusername').value;
 		var psw=document.getElementById('txtpsw').value;
 		if(username.includes("'") || username.includes('"')){
			alert('Username cannot contain quotes and double quotes');
			return;
		}
		if(psw.includes("'") || psw.includes('"')){
			alert('Password cannot contain quotes and double quotes');
			return;
		}
		document.getElementById('btnlogin').disabled=true;
 		var req=new XMLHttpRequest();
 		req.onreadystatechange=function(){
 			if(this.readyState==4&&this.status==200){
 				if(this.responseText=="ERROR"){
 					document.getElementById('errorspace').innerHTML='Wrong username or password.';
 					document.getElementById('btnlogin').disabled=false;
 				}else{
 					window.location.replace("home.php?token="+this.responseText);
 				}
 			}
 		}
 		req.open("POST","funcs/login.php",true);
 		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 		req.send("username="+username+"&password="+psw);
 	}
 </script>
 </body>
</html>






