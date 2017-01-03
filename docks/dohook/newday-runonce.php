<?php
	$sql = "update ".DB::prefix("module_userprefs")." set value=0 where value<>0 and setting='fishmap' and modulename='docks'";
	DB::query($sql);
	$sql = "update ".DB::prefix("module_userprefs")." set value=0 where value<>0 and setting='fishingtoday' and modulename='docks'";
	DB::query($sql);

?>