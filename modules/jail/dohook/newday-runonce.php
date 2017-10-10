<?php
if (get_module_setting('runonce') && get_module_setting('moredays'))
{
	$sql = "update ".DB::prefix("module_userprefs")." set value=value-1 where value>0 and setting='daysin'
	 and modulename='jail'";
	DB::query($sql);
}
if (get_module_setting('runonce') && !get_module_setting('moredays'))
{
	$sql = "update ".DB::prefix("module_userprefs")." set value=0 where value<>0 and setting='injail'
	 and modulename='jail'";
	DB::query($sql);
}
?>