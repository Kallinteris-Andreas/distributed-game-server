<html>
<head>
<title>Sign Up</title>
<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<div class='mainpage loginmain'>
<h1 style='text-align:center'>Sign up</h1>
<p>Enter the required information below:<p>
Username: <input id="txtusername" type="text" maxlength="50" name="username"> <p>
Password: <input id="txtpsw" type="password" maxlength="100" name="password"> <p>
Confirm password: <input id="txtconfpsw" type="password" maxlength="100"> <p>
<span style="color:red" id='errorStr'></span><p>
<input type="button" class='bigbtn' value="Submit" onclick="submitForm()" id='btnsub'>
<input type="button" class='bigbtn' value="Cancel" onclick="window.location.href='index.php'">
<div>
<script>
function submitFinished(req){
	var res=req.responseText;
	if(res=='OK'){
		window.location.href='index.php';
	}else{
		document.getElementById('errorStr').innerHTML=res.substr(6).trim();
		document.getElementById('btnsub').disabled=false;
	}
}
function submitForm(){
	var usrname=document.getElementById('txtusername').value;
	if(usrname.includes("'") || usrname.includes('"') || usrname.includes(';')){
		alert('Username cannot contain quotes, double quotes and semicolons');
		return;
	}
	var psw=document.getElementById('txtpsw').value;
	var psw2=document.getElementById('txtconfpsw');
	if(psw!=psw2.value){
		alert('Passwords must match');
		return;
	}
	if(psw.includes("'") || psw.includes('"') || psw.includes(';')){
		alert('Password cannot contain quotes, double quotes and semicolons');
		return;
	}
	document.getElementById('btnsub').disabled=true;
	var req=new XMLHttpRequest();
	req.onreadystatechange=function(){
		if(this.readyState==4 && this.status==200){
			submitFinished(this);
		}
	}
	req.open("POST",'funcs/addUser.php',true);
	req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	req.send('username='+usrname+'&password='+psw);
}
</script>
</body>
</html>
