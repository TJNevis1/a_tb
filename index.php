<?php
	//echo '<' . rand(2, 9) . '>';

	//echo (isset($_GET['rfid_serial'])) ? '<1>' : '<2>';

	require_once 'db/mongo_functions.php';
	require_once 'lib/php-sdk/src/facebook.php';

	$mongoDB = new MongoFunctions('arduino', 'localhost');

	if(isset($_GET['code'])):
		$credentials = array(
				'appId' => '329588640467656',
				'secret' => '27aa3ba84e9716d1a0f46cbfd706f647'
			);
			
		$facebook = new Facebook($credentials);
	
		$access_token = file_get_contents('https://graph.facebook.com/oauth/access_token?client_id=' . $credentials['appId'] .'&redirect_uri=http://173.248.130.120/&client_secret=' . $credentials['secret'] . '&code=' . $_GET['code']);
		$access_token = split('&', $access_token); //split at &...the string ends with &expires=8947893 (expiration in number of seconds)
		$access_token = substr($access_token[0], 13); //Remove 'access_token='

		$facebook->setAccessToken($access_token);

//echo '<pre>';
$u = $facebook->api('/me?fields=first_name,last_name,hometown,location,gender,timezone');
//echo '</pre>';

		$save_user_info = array(
			'rfid_serial' => $_GET['state'],
			'fb_uid' => $u['id'],
			'first_name' => $u['first_name'],
			'last_name' => $u['last_name'],
			'access_token' => $access_token);

		if(isset($u['hometown']))
			$save_user_info['hometown'] = $u['hometown'];

		if(isset($u['location']))
			$save_user_info['location'] = $u['location'];

		if(isset($u['gender']))
			$save_user_info['gender'] = $u['gender'];

		if(isset($u['timezone']))
			$save_user_info['timezone'] = $u['timezone'];

		$mongoDB->mongo_insert_update('rfid_cards', array('fb_uid' => $u['id']), $save_user_info);


		//Clear the Facebook login session
		//Doing a CURL or file_get_contents to this URL didn't work, but having the browser redirect with javascript does
		//echo '<script>window.location = "' . $facebook->getLogoutUrl() . '";</script>';
		//Custom URL
		//https://www.facebook.com/logout.php?next=http://tjnevis.dlinkddns.com:8080&access_token=<access_token>

		

	elseif(isset($_GET['rfid_serial'])):
		$rfid_found = $mongoDB->findOne('rfid_cards', array('rfid_serial' => $_GET['rfid_serial']));
	
		$result = ($rfid_found) ? 1 : 0;
		echo '<' . $result . '>';
	
		//file_put_contents('test.txt', $_GET['rfid_serial']);
		//file_put_contents('test.txt', json_encode($rfid_found));

		//For some reason, this page waits and times out, so then loading this page does the same, because it's synchronous.  It's smarter to make an asynchronous call to this
		//file_get_contents('http://tjnevis.dlinkddns.com:8000/arduino-socketio/' . $result);


		//I ping the server route arduino-socketio with the rfid_serial and result as parameters in the route, asychronously with curl (the quick timeout makes it asynchronous-like).  The route executes the arduinoUpdate events in the connected clients, which will pop open a Facebook window (if result == 0) or pops up an alert (if result == 1)
		$ch = curl_init();
    		curl_setopt($ch, CURLOPT_URL, 'http://173.248.130.120:8000/arduino-socketio/' . $_GET["rfid_serial"] .'/' . $result);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    		curl_exec($ch);
    		curl_close($ch);


		if($rfid_found):
			$credentials = array(
				'appId' => '329588640467656',
				'secret' => '27aa3ba84e9716d1a0f46cbfd706f647'
			);
			
			$facebook = new Facebook($credentials);

			$facebook->setAccessToken($rfid_found['access_token']);

			$facebook->api('me/feed', 'post', array('message' => 'TESTING!!!!!', 'link' => 'http://tjnevis.dlinkddns.com:8080', 'name' => 'Test post from the Arduino RFID', 'caption' => 'Test post', 'description' => 'From the Arduino RFID'));
		endif;


	endif;
?>

