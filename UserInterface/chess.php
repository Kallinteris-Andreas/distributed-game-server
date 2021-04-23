<?php 
if(isset($_GET['token'])){
	$cq=curl_init();
	curl_setopt($cq,CURLOPT_URL,'http://authmanager:42069/validateToken');
	curl_setopt($cq, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($cq,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	$postValue=json_encode(array('token'=>$_GET['token']));
	curl_setopt($cq,CURLOPT_POSTFIELDS,$postValue);
	$response=curl_exec($cq);
	if($response==false || curl_getinfo($cq, CURLINFO_HTTP_CODE)!=200){
		exit(header("Location: index.php"));
	}else{
		$res=json_decode($response,true);
		$role=$res['role'];
		$username=$res['username'];
	}
	curl_close($cq);
}else{
	exit(header("Location: index.php"));
}
?>
	<html>
	 <head>
	  <title>Chess play</title>
	  <link rel="stylesheet" type="text/css" href="styles.css">
	  <link rel="stylesheet"
      href="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.css"
      integrity="sha384-q94+BZtLrkL1/ohfjR8c6L+A6qzNH9R2hBLwyoAfu3i/WCvQjzL2RQJ3uNHDISdU"
      crossorigin="anonymous">
	 </head>
	 <body>
	 	<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2"
        crossorigin="anonymous"></script>

		<script src="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.js"
        integrity="sha384-8Vi8VHwn3vjQ9eUHUxex3JSN/NFqUg3QbPyX8kWyb93+8AC/pPWTzj+nHtbC5bxD"
        crossorigin="anonymous"></script>

	 	<div class='menubar'>
	 		<ul>
	 			<li><a href='home.php?token=<?php echo($_GET["token"]) ?>'>Home</a></li>
	 			<?php 
	 				if($role[1]=="1"){
	 					echo("<li><a href='official.php?token=".$_GET["token"]."'>Tournaments</a></li>");
	 				}
	 				if($role[2]=="1"){
	 					echo("<li><a href='admin.php?token=".$_GET["token"]."'>Administration</a></li>");
	 				}
	 			?>
	 			<li><a style='float:right' href='index.php?token=<?php echo($_GET["token"]) ?>'>Log out</a></li>
	 		</ul>
		</div>
		
		<div class='mainpage'>
			
		</div>

		
	 </body>
	</html>






