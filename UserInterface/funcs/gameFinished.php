<?php
//Input: nothing
//Returns: nothing

$cq=curl_init();
curl_setopt($cq,CURLOPT_URL,'http://gamemaster:8080/gameFinished');
curl_exec($cq);
curl_close($cq);