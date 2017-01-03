<?php
	$skills = DB::prefix("skills");
	$skillsbuffs = DB::prefix("skillsbuffs");
	$sql = "DROP TABLE $skills, $skillsbuffs";
	DB::query($sql);
?>