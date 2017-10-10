<?php
while (list($key,$trans)=each($transintext))
	{
	$intext = addslashes(rawurldecode($transintext[$key]));  //addslashes because  this comes in encoded and only plain text ... it's from the checkboxes directly
	$outtext = $intext;
	//debug($intext);
	$login = $session['user']['login'];
	$sql = "INSERT INTO " . DB::prefix("translations") . " (language,uri,intext,outtext,author,version) VALUES" . " ('$languageschema','$namespace','$intext','$outtext','$login','$logd_version')";
	DB::query($sql);
	invalidatedatacache("translations-".$namespace."-".$languageschema);
	$sql = "DELETE FROM " . DB::prefix("untranslated") . " WHERE BINARY intext = '$intext' AND language = '$languageschema' AND namespace = '$namespace'";
	DB::query($sql);
	}
?>