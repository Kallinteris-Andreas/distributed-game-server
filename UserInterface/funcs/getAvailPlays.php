<?php 
//params: token(GET)
//returns: forwards result from playMaster/availPlays
set_time_limit(0);
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
	exit(header("Location: index.php"));
}
if($role[0]!=1){
	http_response_code(500);
	exit();
}
$alivePlayMaster=false;
while($alivePlayMaster===false){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://playmaster:8080/availPlays');
	curl_setopt($cq,CURLOPT_POST,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('username'=>$username));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	$alivePlayMaster=curl_exec($cq);
	curl_close($cq);
}

?>