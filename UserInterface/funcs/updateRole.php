<?php 
//GET params: token
//POST params as JSON: username,newrole
//returns: 200/500 code

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
	http_response_code(500);
	exit();
}
if($role[2]!=1){
	http_response_code(500);
	exit();
}
$data = json_decode(file_get_contents('php://input'), true);
if(!isset($data['username'])|| !isset($data['newrole'])){
	http_response_code(500);
	exit();
}
$cq=curl_init();
curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/updateRole');
curl_setopt($cq,CURLOPT_POST,true);
curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
$postValue=json_encode(array('username'=>$data['username'],'newrole'=>$data['newrole']));
curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
curl_exec($cq);
if(curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
	http_response_code(500);
}
curl_close($cq);
?>