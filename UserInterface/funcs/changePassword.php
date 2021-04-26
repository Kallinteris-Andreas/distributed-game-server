<?php 
//POST params: token,password
//returns: <nothing>
if (isset($_POST['token']) && isset($_POST['password'])){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/changePassword');
	curl_setopt($cq,CURLOPT_POST,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('token'=>$_POST['token'],'newpassword'=>$_POST['password']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
	$response=curl_exec($cq);
	curl_close($cq);
}else{
	echo('ERROR');
}
?>