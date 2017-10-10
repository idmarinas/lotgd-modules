<?php
while (list($key,$trans)=each($transintext)) {
	if ($transintext[$key]<>"")
		{
		$intext = addslashes(rawurldecode($transintext[$key]));
		//debug($intext);
		$sql = "DELETE FROM " . DB::prefix("untranslated") . " WHERE BINARY intext = '$intext' AND language = '$languageschema' AND namespace = '$namespace'";
		DB::query($sql);
		}
}
?>