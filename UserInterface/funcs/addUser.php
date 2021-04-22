<?php 
//POST params: username,password
//returns OK or error message

if(!isset($_POST['username'])||!isset($_POST['password'])){
	echo("ERROR: missing params");
	exit();
}
$data=json_encode(array('username'=>$_POST['username'],'password'=>$_POST['password']));
$q=curl_init();
curl_setopt($q,CURLOPT_URL,'http://authmanager:42069/createUser');
curl_setopt($q,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
curl_setopt($q,CURLOPT_POST,true);
curl_setopt($q,CURLOPT_POSTFIELDS,$data);
curl_exec($q);
if(curl_getinfo($q, CURLINFO_HTTP_CODE)!=200){
	echo('ERROR: Username already exists');
}else{
	echo('OK');
}
curl_close($q);
?>