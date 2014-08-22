<?php

/*
* Ron's Comment System
*
* Disqus Comments Importer
*
* @author: Ron Bühler <ronbuehler@live.de>
*/

	include_once('../../classes/comments.php');
	$comments = new comments();

//Einträge von Disqus einlesen
	$disqusData = array();

	$xml = new DOMDocument('1.0', 'utf-8');
	$xml->preserveWhiteSpace = false;
	$xml->formatOutput = true;

	$importFilePath = 'import';
	$xml->load($importFilePath);

	//Threads
	$elements = $xml->getElementsByTagName("thread");
	foreach($elements as $node){
		$episodeID = "";
		$threadID = $node->getAttribute('dsq:id');
	  foreach($node->childNodes as $child) {
	  	if ($child->nodeName == "id") $episodeID = $child->nodeValue;
	  }
		if (!isset($disqusData[$threadID])) $disqusData[$threadID]["episode"] = $episodeID;
	}

	//Posts
	$elements = $xml->getElementsByTagName("post");
	foreach($elements as $node){
		$elementData = array();
		$threadID = "";
	  foreach($node->childNodes as $child) {
	  	if ($child->nodeName == "thread") $threadID = $child->getAttribute('dsq:id');
	  	if ($child->nodeName == "author") {
	  		foreach($child->childNodes as $author) {
			  	if (in_array($author->nodeName, array("name", "email"))) $elementData[$author->nodeName] = utf8_decode($author->nodeValue);
	  		}
	  	}
	  	if ($child->nodeName == "createdAt") {
	  		$postdate = date_create_from_format('Y-m-d H:i:s', str_replace(array("T", "Z"), "", $child->nodeValue));
				$elementData[$child->nodeName] = date_format($postdate, 'U');
			}
	  	if (in_array($child->nodeName, array("message", "isDeleted", "ipAddress"))) $elementData[$child->nodeName] = utf8_decode($child->nodeValue);
	  }

		if (in_array($threadID, array_keys($disqusData)) && $elementData["isDeleted"] == "false") $disqusData[$threadID]["posts"][] = $elementData;
	}

	//Kommentardatei schreiben
	foreach ($disqusData as $thread) {
		if (!isset($thread["episode"]) || !isset($thread["posts"])) continue;
		$epiID = $thread["episode"];
		foreach ($thread["posts"] as $post) {
			$commentData = array();

			$commentData['author'] = utf8_encode($post["name"]);
			$commentData['email'] = utf8_encode($post["email"]);
			$commentData['website'] = "";
			$commentData['message'] = utf8_encode($post["message"]);

			$commentData['id'] = md5($post["ipAddress"].$post["createdAt"]);
			$commentData['ip'] = md5($post["ipAddress"]);
			$commentData['level'] = 0;
			$commentData['replytoid'] = 0;
			$commentData['moderate'] = 1;
			$commentData['time_created'] = $post["createdAt"];

			$commentFilePath = realpath('../').'/files/'.$epiID.'.xml';


			$xml = new DOMDocument('1.0', 'utf-8');
			$xml->preserveWhiteSpace = false;
			$xml->formatOutput = true;

			if (is_writable($commentFilePath)) {
				$xml->load($commentFilePath) or die();
				$root = $xml->getElementsByTagName("comments")->item(0);
			} else {
				$root = $xml->createElement('comments');
				$xml->appendChild($root);
			}

			$comment = $xml->createElement('comment');
				$id = $xml->createAttribute('id');
				$id->value = $commentData['id'];
				$comment->appendChild($id);

				$replytoid = $xml->createAttribute('replytoid');
				$replytoid->value = $commentData['replytoid'];
				$comment->appendChild($replytoid);

				$ip = $xml->createAttribute('ip');
				$ip->value = $commentData['ip'];
				$comment->appendChild($ip);

				$level = $xml->createAttribute('level');
				$level->value = $commentData['level'];
				$comment->appendChild($level);

				$moderate = $xml->createAttribute('moderate');
				$moderate->value = $commentData['moderate'];
				$comment->appendChild($moderate);

				$time = $xml->createAttribute('time_created');
				$time->value = $commentData['time_created'];
				$comment->appendChild($time);

				$author = $xml->createAttribute('author');
				$author->value = $commentData['author'];
				$comment->appendChild($author);

				$email = $xml->createAttribute('email');
				$email->value = $commentData['email'];
				$comment->appendChild($email);

				$website = $xml->createAttribute('website');
				$website->value = $commentData['website'];
				$comment->appendChild($website);

				$message = $xml->createElement('message');
				$messageText = $xml->createTextNode($commentData['message']);
				$message->appendChild($messageText);
				$comment->appendChild($message);
			$root->appendChild($comment);

			$xml->save($commentFilePath) or die();
		}
	}

	echo "Import durchgeführt!";

?>