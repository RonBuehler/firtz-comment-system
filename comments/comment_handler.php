<?php

/*
* Ron's Comment System
*
* Comment Handler
*
* @author: Ron Bühler <ronbuehler@live.de>
*/

session_start();
header('Content-type: application/json');

function errorResponse ($messsage) {
	$_SESSION['rfcs_success'] = false;
	die(json_encode(array("return" => "false", "error" => utf8_encode($messsage))));
}

if (trim($_POST["author"]) == "") {
	errorResponse('Du musst einen Namen angeben!');
}

if (trim($_POST["email"]) == "" || !preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["email"])) {
	errorResponse('Du musst eine gültige E-Mail-Adresse angeben!');
}

if (trim($_POST["message"]) == "") {
	errorResponse('Du musst ein Kommentar eingeben!');
}

if (trim($_POST["code"]) == "") {
	errorResponse('Du musst den Code eingeben!');
} elseif (strtolower($_POST["code"]) != strtolower($_SESSION['rfcs_captcha_'.$_POST["epiID"]])) {
	errorResponse('Der eingegebene Code ist falsch!');
}

//if (isset($_SESSION['rfcs_captcha_'.$_POST["epiID"]])) unset($_SESSION['rfcs_captcha_'.$_POST["epiID"]]);

writeComment();


function writeComment() {

	if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	 $client_ip = $_SERVER['REMOTE_ADDR'];
	} else {
	 $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	$actTime = time();

	$formData = array();
	foreach ($_POST as $name => $value) {
		$formData[$name] = $value;
	}

	//$intent->request_id = openssl_digest($ip_string . $ua_string, 'sha256');

	$formData['id'] = md5($client_ip.$actTime);
	$formData['ip'] = md5($client_ip);
	$formData['level'] = 0;
	$formData['moderate'] = 1;
	$formData['time_created'] = $actTime;

	//		$tz = new DateTimeZone('Europe/Berlin');
	//		$date_today = new DateTime();
	//		$date_today->setTimezone($tz);

	$commentFilePath = '../comments/files/'.$formData['epiID'].'.xml';
	$xml = new DOMDocument('1.0', 'utf-8');
	$xml->preserveWhiteSpace = false;
	$xml->formatOutput = true;

	$xml->load($commentFilePath) or die(json_encode(array("return" => "false", "error" => utf8_encode("Fehler beim Laden!"))));

	$root = $xml->getElementsByTagName("comments")->item(0);

		$comment = $xml->createElement('comment');
			$id = $xml->createAttribute('id');
			$id->value = $formData['id'];
			$comment->appendChild($id);

			$replytoid = $xml->createAttribute('replytoid');
			$replytoid->value = $formData['replytoid'];
			$comment->appendChild($replytoid);

			$ip = $xml->createAttribute('ip');
			$ip->value = $formData['ip'];
			$comment->appendChild($ip);

			$level = $xml->createAttribute('level');
			$level->value = $formData['level'];
			$comment->appendChild($level);

			$moderate = $xml->createAttribute('moderate');
			$moderate->value = $formData['moderate'];
			$comment->appendChild($moderate);

			$time = $xml->createAttribute('time_created');
			$time->value = $formData['time_created'];
			$comment->appendChild($time);

			$author = $xml->createAttribute('author');
			$author->value = $formData['author'];
			$comment->appendChild($author);

			$email = $xml->createAttribute('email');
			$email->value = $formData['email'];
			$comment->appendChild($email);

			$website = $xml->createAttribute('website');
			$website->value = $formData['website'];
			$comment->appendChild($website);

			$message = $xml->createElement('message');
			$messageText = $xml->createTextNode($formData['message']);
			$message->appendChild($messageText);
			$comment->appendChild($message);
		$root->appendChild($comment);

	$xml->save($commentFilePath) or die(json_encode(array("return" => "false", "error" => utf8_encode("Fehler beim Speichern!"))));

	$_SESSION['rfcs_success'] = true;
	echo json_encode(array("return" => "true", "error" => ""));
}

?>