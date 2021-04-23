<?php 
//params: token(GET)
//returns: forwards result from playMaster/availPlays


//below is for testing
echo('{"plays":[{"playId":5,"tournamentName":"","opponent":"noobPractice","gameType":"chess"},{"playId":2,"tournamentName":"WorldCup","opponent":"NotNoobNotPractice","gameType":"tictactoe"}]}');
//echo('{"plays":[]}');
exit();


if(isset($_GET['token'])){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/validateToken');
	curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('token'=>$_GET['token']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	$response=curl_exec($cq);
	if($response==false || curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
		echo('ERROR');
	}else{
		$res=json_decode($response,true);
		$username=$res['username'];
	}
	curl_close($cq);
}else{
	exit(header("Location: index.php"));
}

$cq=curl_init();
curl_setopt($cq,CURLOPT_URL,'http://playMaster:8080/availPlays');
curl_setopt($cq,CURLOPT_POST,true);
curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
$postValue=json_encode(array('username'=>$username));
curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
curl_exec($cq);
curl_close($cq);

?>