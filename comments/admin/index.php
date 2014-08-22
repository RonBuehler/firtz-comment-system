<?php
/*
* Ron's Comment System
*
* Admin Area
*
* @author: Ron Bühler <ronbuehler@live.de>
*/

	include_once('../../classes/comments.php');
	$comments = new comments();

	session_start();

	if (isset($_REQUEST['action'])) {
		if ($_REQUEST['action'] == 'showcomm' && isset($_REQUEST['ep']) && isset($_REQUEST['id'])) {
			$retFlag = $comments->moderateComment(realpath('../').'/files/'.$_REQUEST['ep'].'.xml', $_REQUEST['id'], 0);
		} else if ($_REQUEST['action'] == 'hidecomm' && isset($_REQUEST['ep']) && isset($_REQUEST['id'])) {
			$retFlag = $comments->moderateComment(realpath('../').'/files/'.$_REQUEST['ep'].'.xml', $_REQUEST['id'], 1);
		} else if ($_REQUEST['action'] == 'delcomm' && isset($_REQUEST['ep']) && isset($_REQUEST['id'])) {
			$retFlag = $comments->delComment(realpath('../').'/files/'.$_REQUEST['ep'].'.xml', $_REQUEST['id']);
		}
	}

	$selFilter = "both";
	if (isset($_SESSION['selFilter']) && !isset($_REQUEST['selFilter'])) {
		$selFilter = $_SESSION['selFilter'];
	} else if (isset($_REQUEST['selFilter'])) {
		$selFilter = $_SESSION['selFilter'] = $_REQUEST['selFilter'];
	}

//Kommentare einlesen
	$commentsData = array();
	foreach (new DirectoryIterator(realpath('../').'/files/') as $fileInfo) {
	  if($fileInfo->isDot()) continue;
	  if (strtolower($fileInfo->getExtension()) != 'xml') continue;

		$commentFilePath = realpath('../').'/files/'.$fileInfo->getBasename('.xml').'.xml';
		foreach($comments->readCommentFile($commentFilePath) as $key => $val) {
			$actData = unserialize($val);
			$commentsData[$fileInfo->getBasename('.xml')][] = $actData;
		}
	}

	$sitecontent  = '<html>';
	$sitecontent .= '<head>';
	$sitecontent .= '<script src="../../js/jquery.js"></script>';
			$sitecontent .= '<style type="text/css">';
				$sitecontent .= '.ron-commentform_comment-container {width: 500px; margin-bottom: 20px; border: 1px #000 solid; padding: 5px;display: inline-block; vertical-align: top;background-color: #C9C9C9;}';
				$sitecontent .= '.ron-commentform_comment-container_active {width: 500px; margin-bottom: 20px; border: 1px #000 solid; padding: 5px;display: inline-block; vertical-align: top;background-color: #BCFFB0;}';
				$sitecontent .= '.ron-commentform_commenthead {font-weight:bold; border-bottom: 1px #000 solid;}';
				$sitecontent .= '.ron-commentform_commenthead a {text-decoration:none;}';
				$sitecontent .= '.ron-commentform_commentmsg {padding-bottom:1em;}';
				$sitecontent .= '.ron-commentform_commentmsg a {text-decoration:none; font-weight:bold;}';
			$sitecontent .= '}';
			$sitecontent .= '</style>';
	$sitecontent .= '</head>';
	$sitecontent  .= '<body>';

	$navcontent  = '<form id="filterform" method="post" action="" >';
	$navcontent .= '<select name="selFilter" id="selFilter" onChange="">';
	$navcontent .= '<option value="both"'.(($selFilter == "both") ? ' selected="selected"' : '').'>Alle</option>';
	$navcontent .= '<option value="hidden"'.(($selFilter == "hidden") ? ' selected="selected"' : '').'>Ausgeblendete</option>';
	$navcontent .= '<option value="shown"'.(($selFilter == "shown") ? ' selected="selected"' : '').'>Angezeigte</option>';
	$navcontent .= '</select>';
	$navcontent .= '<input id="btnFilter" name="btnFilter" type="submit" value="Filtern" style="margin-left: 10px;">';
	$navcontent .= '</form>';


	$bodycontent = $navcontent;


	if (count($commentsData) > 0) {
		foreach($commentsData as $episode => $arrComments) {
			foreach($arrComments as $actComment) {
				if ($selFilter == "hidden" && $actComment['moderate'] == 0) continue;
				if ($selFilter == "shown" && $actComment['moderate'] == 1) continue;

				$commStatus = "";
				if ($actComment['moderate'] == 0) $commStatus = "_active";

				$bodycontent .= "<b>".$episode."</b><br />";
				$bodycontent .= '<div class="ron-commentform_comment-container'.$commStatus.'">';

					$bodycontent .= '<div class="ron-commentform_commenthead">';
					if (isset($actComment['email']) && trim($actComment['email']) != '') {
						$bodycontent .= '<a href="mailto:'.$actComment['email'].'">'.utf8_decode($actComment['author']).'</a>';
					} else {
						$bodycontent .= utf8_decode($actComment['author']);
					}
					if (isset($actComment['website']) && trim($actComment['website']) != '') $bodycontent .= ' - '.'<a href="'.$actComment['website'].'">'.$actComment['website'].'</a>';
					$bodycontent .= '</div>';

					$bodycontent .= '<div class="ron-commentform_commentmsg">'.utf8_decode($actComment['message']).'</div>';

						$bodycontent .= '<div style="align:left; float:left;">';
							$bodycontent .= '<small>';
								$bodycontent .= date('d.m.Y h:m', $actComment['time_created']).' Uhr &nbsp;&nbsp;';
							$bodycontent .= '</small>';
						$bodycontent .= '</div>';

						$bodycontent .= '<div style="align:right; float:right;">';
							$bodycontent .= '<small>';

								//Anzeigen/Ausblenden
								if ($actComment['moderate'] == 1) {
									$bodycontent .= askConfirmMain("show", $actComment['id']);
									$bodycontent .= '<a class="showClass_'.$actComment['id'].'" href="index.php?action=showcomm&ep='.$episode.'&id='.$actComment['id'].'" style="margin-right: 10px;">'.'Anzeigen'.'</a>';
								} else {
									$bodycontent .= askConfirmMain("hide", $actComment['id']);
									$bodycontent .= '<a class="hideClass_'.$actComment['id'].'" href="index.php?action=hidecomm&ep='.$episode.'&id='.$actComment['id'].'" style="margin-right: 10px;">'.'Ausblenden'.'</a>';
								}

								//Löschen
								$bodycontent .= askConfirmMain("del", $actComment['id']);
								$bodycontent .= '<a class="delClass_'.$actComment['id'].'" href="index.php?action=delcomm&ep='.$episode.'&id='.$actComment['id'].'" style="margin-right: 10px;">'.'Löschen'.'</a>';

							$bodycontent .= '</small>';
						$bodycontent .= '</div>';

					$bodycontent .= '</div>';
				$bodycontent .= '<div style="clear:both;"></div>';
			}
		}
	}



function askConfirmMain($type = "", $id = "") {
	if ($type == "show") {
		$Message = "Dieses Kommentar wirklich auf der Webseite anzeigen?";
		$typeClass = "showClass_".$id;
	} elseif ($type == "hide") {
		$Message = "Dieses Kommentar wirklich ausblenden?";
		$typeClass = "hideClass_".$id;
	} elseif ($type == "del") {
		$Message = "Dieses Kommentar wirklich löschen?";
		$typeClass = "delClass_".$id;
	}

	if ($type != "") {
		$return = "<script>";
			$return .= "$(document).ready(function() {";
				$return .= "$('.".$typeClass."').click (function () {";
					$return .= "return confirm ('".$Message."') ;";
				$return .= "}) ;";
			$return .= "});";
  	$return .= "</script>";
  }

  return $return;
}




  $sitecontent .= $bodycontent;

	$sitecontent .= '</body>';
	$sitecontent .= '</html>';

	echo $sitecontent;
?>