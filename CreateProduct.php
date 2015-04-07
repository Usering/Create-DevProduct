<?php
	$url = "http://www.roblox.com/places/developerproducts/add";
	$username = "Shedletsky"; //the username of the user with the place
	$password = "hunter2"; //the password of the user

	$robuxPrice = $_GET["robuxPrice"];
	$tixPrice = $_GET["tixPrice"];
	$name =  $_GET["name"];
	$universeId = $_GET["universeId"]; //this is not the placeId!
	/*

	To get the universeId, you have to on a place in your universe, click "Configure Game"
	The universeId will be in the URL bar after "?id="
	http://www.roblox.com/universes/configure?id=100392728 and the universeId would be 100392728

	You have to send a GET request to make this page work
	The url you have to send the request to will look a bit like this:
	http://YOURDOMAIN/CreateProduct.php?robuxPrice=PRICEINROBUX&tixPrice=PRICEINTICKETS&name=PRODUCTNAME&universeId=UNIVERSEID
	This script has to be hosted on a webserver with PHP installed or else it won't work
	*/

	if (!isset($robuxPrice)) {
		$robuxPrice = 0;
	}
	if (!isset($tixPrice)) {
		$tixPrice = 0;
	}

	if ($tixPrice<0) {
		$tixPrice = 0;
	}
	if ($robuxPrice<0) {
		$robuxPrice = 0;
	}

	function curl($url, $post=false, $cookie=false,$csrftoken = ""){
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url );
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			'Connection: keep-alive',
			'Accept: */*',
			'X-requested-with: XMLHTMLRequest',
			'Access-Control-Allow-Credentials: true',
			'Access-Control-Allow-Origin: http://www.roblox.com',
			'X-CSRF-TOKEN: ' . $csrftoken
		));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A";
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	    if($cookie){
	        curl_setopt($ch, CURLOPT_COOKIEFILE, md5( SHA1($username) . SHA1($password) ));
	        curl_setopt($ch, CURLOPT_COOKIEJAR, md5( SHA1($username) . SHA1($password) ));
	    }
	    if($post){
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	    }
	    return  curl_exec($ch);
	}
	 
	function Login($Username, $Password){
	        file_put_contents($Username, '');
	        $Login = curl('https://m.roblox.com/Login', ('UserName=' . $Username . '&Password=' . $Password), $Username);
	                if (stristr($Login, 'Object moved')){
	                return true;
	        }else{
	                return false;
	        }
	}

	if (Login($username,$password)) {
		$token = "";
		$source =  curl("http://www.roblox.com/Forum/default.aspx",false,$username);
		$start = strpos($source,"Roblox.XsrfToken.setToken('");
		$end = strpos(substr($source,$start),"');");
		$token = substr($source,$start,$end);
		$token = substr($token,strlen("Roblox.XsrfToken.setToken('"));
		$post = array(
			"universeId" => $universeId,
			"name" => $name,
			"developerProductId" => 0,
			"priceInRobux" => $robuxPrice,
			"priceInTickets" => $tixPrice,
			"description" => ""
		);
		$body = curl($url,$post,$username,$token);
		$response = array();
		preg_match("/Product ([\d]+)/",$body, $response);
		/*
			Below is going to return JSON, here is how to handle it:

			With this response, you want to decode it with HttpService in game
			local ret = game.HttpService:JSONDecode(GETASYNC_RESPONSE,true)
			local productId = tonumber(ret["productId"])
			local response = ret["response"]
			print("Response:",response,"ProductId:",productId)
		*/
		if ($response[1]){
			echo json_encode(array(
				"response" => "success",
				"productId" => $response[1]
			));
		} else {
			echo json_encode(array(
				"response" => "unknown error",
				"productId" => 0
			));
		};
	}
?>
