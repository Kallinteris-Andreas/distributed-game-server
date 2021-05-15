<?php 
//GET params: token
//POST params: gameType('chess','tictactoe'),playId
//returns: 200&gameState/404&''
set_time_limit(0);
if(isset($_GET['token'])&&isset($_POST['gameType'])&&isset($_POST['playId'])){
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
if($role[0]!=1){
	http_response_code(500);
	exit();
}
$result=false;
$code=0;
while($result===false){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://playmaster:8080/getPlay');
	curl_setopt($cq,CURLOPT_POST,true);
	curl_setopt($cq,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('username'=>$username,'gameType'=>$_POST['gameType'],'playId'=>(int)$_POST['playId']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	$result=curl_exec($cq);
	$code=curl_getinfo($cq, CURLINFO_HTTP_CODE);
	curl_close($cq);
}
http_response_code($code);
if(http_response_code()==200){
	$decresult=json_decode($result,true);
	echo($decresult['gameState']);
}
?>