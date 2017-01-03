<?
	$sql = "update ".DB::prefix("module_userprefs")." set value=value+1 where value<>0 and setting='daysdeputy' and modulename='djail'";
	DB::query($sql);

?>