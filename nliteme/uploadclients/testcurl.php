<?php
	$target_url = 'http://localhost/nliteme/upload.php';

	$jsonstring = '{
"0":{"build":{"realname":"build","value":"30.00_2013-02-15-12-03"},
	 "increment":{"realname":"increment","value":"30.00"},
	 "description":{"realname":"description","value":"This is a VERY LONG description"}
	 },
"1":{"build":{"realname":"build","value":"40.00_2013-02-15-12-03"},
	 "increment":{"realname":"increment","value":"40.00"},
	 "description":{"realname":"description","value":"This is another VERY LONG description"}
	}
}';

	$post = array('uploadtype'=>'builds','jsonstring'=>$jsonstring);
 
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$target_url);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                                                                   
	$result=curl_exec ($ch);
	curl_close ($ch);
	echo $result;
?>
