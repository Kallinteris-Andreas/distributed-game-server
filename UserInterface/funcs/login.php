<?php 
//POST params: username,password
//returns: token or 'ERROR'
if (isset($_POST['username']) && isset($_POST['password'])){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/login');
	curl_setopt($cq,CURLOPT_POST,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('username'=>$_POST['username'],'password'=>$_POST['password']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
	$response=curl_exec($cq);
	if($response==false || curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
		echo('ERROR');
		exit();
	}else{
		$res=json_decode($response,true);
		if(!isset($res['token'])){
			echo('ERROR');
			exit();
		}
		echo($res['token']);
	}
	curl_close($cq);
}else{
	echo('ERROR');
}
?>