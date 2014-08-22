<?php

/*
* Ron's Comment System
*
* Comment Class
*
* @author: Ron Bühler <ronbuehler@live.de>
*/


	class comments {

		function __construct($main = null) {
			if ($main != null) {
				$main->set('feedattr_default',array_merge($main->get('feedattr_default'), array('commentsystem')));
				$main->set('UI', $main->get('UI').'; comments/');

				$main->route('GET|HEAD /comments/@epi',
					function ($main,$params) {
						$comments = $main->get('comments');
						$comments->showComments($main, $params['epi']);
					}, 1
				);
			}
		}



		/* Check if Episode-Commentfile exist. If not, create it.
			 If found, read Comments and render Comments */
		public function showComments($main, $epiID) {

			$commentFilePath = $main->get('BASEPATH').'/comments/files/'.$epiID.'.xml';
			$main->set('epiID', $epiID);

			if (is_writable($commentFilePath)) {
				$commentsData = array();

				foreach($this->readCommentFile($commentFilePath, true) as $key => $val) {
					$actData = unserialize($val);
//					$actData['message'] = nl2br($actData['message']);
//					$logDate = DateTime::createFromFormat('U', $actData['time_created']);
//					$actData['time_created'] = $logDate->format('d.m.Y H:i');

					$commentsData[] = $actData;
				}

				if (count($commentsData) > 0) {
					$main->set('commentsData',array_reverse($commentsData));
					echo Template::instance()->render('tmpl_comments.html');

					if (isset($_SESSION['rfcs_success']) && $_SESSION['rfcs_success']) {
						$thxmsg  = "<div style=\"width: 100%; height: 40xp; padding: 5px; background-color: #00EC00; color: #ffffff;\">";
							$thxmsg .= "DANKE für dein Kommentar, es liegt nun zum Freigeben vor!";
						$thxmsg .= "<div>";

						echo '<script>';
							echo 'document.getElementById(\'thxmsg'.$epiID.'\').innerHTML = \''.$thxmsg.'\';';
						echo '</script>';
						unset($_SESSION['rfcs_success']);
					}

				} else {
					echo '<div style="margin-top: 30px; margin-bottom: 15px;">Noch kein Kommentar vorhanden!</div>';
				}

			} else {
				echo '<div style="margin-top: 30px; margin-bottom: 15px;">Noch kein Kommentar vorhanden!</div>';
				$this->createNewCommentFile($commentFilePath);
			}

			echo '<hr />';
			echo '<div id="ron-cf-placeholder_'.$epiID.'" name="ron-cf-placeholder_'.$epiID.'" style="font-size:20px;">';
				echo 'Neues Kommentar schreiben';
			echo '</div>';


			$img = new Image();
			$img->captcha('AHGBold.ttf',16,5,'SESSION.rfcs_captcha_'.$epiID);
			$main->set('captcha', base64_encode($img->dump()));

			echo Template::instance()->render('tmpl_commentform.html');

		}


		/* Create empty Episode-Commentfile */
		private function createNewCommentFile($commentFilePath) {
			$xml = new DOMDocument('1.0', 'utf-8');
			$xml->preserveWhiteSpace = false;
			$xml->formatOutput = true;

			$root = $xml->createElement('comments');
			$xml->appendChild($root);

			$xml->save($commentFilePath) or die('Error');
		}


		/* Read and return Comments from Episode-Commentfile */
		public function readCommentFile($commentFilePath, $skipmoderate = false) {

			$commentsData = array();

			$xml = new DOMDocument('1.0', 'utf-8');
			$xml->preserveWhiteSpace = false;
			$xml->formatOutput = true;

			$xml->load($commentFilePath);

			$elements = $xml->getElementsByTagName("comment");
			foreach($elements as $node){
				$elementData = array();
				$commentID = $node->getAttribute('id');

				//Noch nicht moderierte überspringen
				if ($skipmoderate && $node->getAttribute('moderate') == 1) continue;

				$elementData["id"] = $node->getAttribute('id');
				$elementData["replytoid"] = $node->getAttribute('replytoid');
				$elementData["ip"] = $node->getAttribute('ip');
				$elementData["level"] = $node->getAttribute('level');
				$elementData["moderate"] = $node->getAttribute('moderate');
				$elementData["time_created"] = $node->getAttribute('time_created');
				$elementData["author"] = $node->getAttribute('author');
				$elementData["email"] = $node->getAttribute('email');
				$elementData["website"] = $node->getAttribute('website');

			  foreach($node->childNodes as $child) {
			  	$elementData[$child->nodeName] = $child->nodeValue;
			  }

				$commentsData[$commentID] = serialize($elementData);
			}

			return $commentsData;
		}

		/* Kommentar moderieren */
		public function moderateComment($commentFilePath, $commID, $setStatus) {
			if ($commentFilePath != '' && $commID != '') {
				if (is_writable($commentFilePath)) {
					$xml = new DOMDocument('1.0', 'utf-8');
					$xml->preserveWhiteSpace = false;
					$xml->formatOutput = true;
					$xml->load($commentFilePath);

					$searchNode = $xml->getElementsByTagName('comment');
					foreach ($searchNode as $actNode) {
						if ($actNode->getAttribute('id') == $commID) {
							if ($actNode->setAttribute('moderate', $setStatus)) {
							} else {
								 return false;
							}
						}
					}

					if ($xml->save($commentFilePath)) {
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}

			return true;
		}

		/* Kommentar löschen */
		function delComment($commentFilePath, $commID) {
			if ($commentFilePath != '' && $commID != '') {
				if (is_writable($commentFilePath)) {
					$xml = new DOMDocument('1.0', 'utf-8');
					$xml->preserveWhiteSpace = false;
					$xml->formatOutput = true;
					$xml->load($commentFilePath);

					$searchNode = $xml->getElementsByTagName('comment');
					foreach ($searchNode as $actNode) {
						if ($actNode->getAttribute('id') == $commID) {
							$actNode->parentNode->removeChild($actNode);
						}
					}

					if ($xml->save($commentFilePath)) {
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}

			return true;
		}


		function time_difference($date, $isUnixTimestap = false) {
			if(empty($date)) {
				return "No date provided";
			}

			$periods         = array(" Sek", " Min", " Std", " Tag", " Woche", " Monat", " Jahr", " Jahrzent");
			$lengths         = array("60","60","24","7","4.35","12","10");

			$now             = time();

			if ($isUnixTimestap) {
				$unix_date = $date;
			}else{
				$unix_date = strtotime($date);
			}

			   // check validity of date
			if(empty($unix_date)) {
				return "Bad date";
			}

			// is it future date or past date
			if($now > $unix_date) {
				$difference     = $now - $unix_date;
				$tense         = "";

			} else {
				$difference     = $unix_date - $now;
				$tense         = "from now";
			}

			for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
				$difference /= $lengths[$j];
			}

			$difference = round($difference);

			if($difference != 1) {
				if ($j == 3 || $j == 5 || $j == 6) $periods[$j].= "e";
				if ($j == 4) $periods[$j].= "n";
			}

			return "$difference$periods[$j] {$tense}";
		}
	}
?>