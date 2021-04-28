<?php 
//GET params: token
//returns: [{username,role},...] / 500

if(isset($_GET['token'])){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/validateToken');
	curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('token'=>$_GET['token']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	$response=curl_exec($cq);
	if($response==false || curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
		http_response_code(500);
		exit();
	}else{
		$res=json_decode($response,true);
		$username=$res['username'];
		$role=$res['role'];
	}
	curl_close($cq);
}else{
	echo('1');
	http_response_code(500);
	exit();
}
if($role[2]!=1){
	echo('2');
	http_response_code(500);
	exit();
}
$cq=curl_init();
curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/listUsers');
curl_setopt($cq,CURLOPT_RETURNTRANSFER,true);
$users=curl_exec($cq);
if(curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
	http_response_code(500);
	echo('4');
}else{
	echo($users);
}
curl_close($cq);
?>