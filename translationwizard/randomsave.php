<?php
$intext = httppost('intext');
$outtext = httppost('outtext');
$namespace = httppost('namespace');
$language = httppost('language');
if ($outtext <> "")	{
	$login = $session['user']['login'];
	$sql = "INSERT INTO " . DB::prefix("translations") . " (language,uri,intext,outtext,author,version) VALUES" . " ('$language','$namespace','$intext','$outtext','$login','$logd_version')";
	$result1=DB::query($sql);
	$sql = "DELETE FROM " . DB::prefix("untranslated") . " WHERE intext = '$intext' AND language = '$language' AND namespace = '$namespace'";
	$result2=DB::query($sql);
	if (!$result1 || !$result2) $error=4;
		else $error=5;
	invalidatedatacache("translations-".$namespace."-".$languageschema);
}
redirect("runmodule.php?module=translationwizard&error=".$error); //just redirecting so you go back to the previous page after the save
?>