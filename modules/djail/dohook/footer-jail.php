<?php
$op = httpget('op');
if (is_module_active("jail") && $op=="talk"){
	if ($session['user']['dragonkills']>=get_module_setting("mindk","dragoneggs")){
		addnav("Apply to Become a Deputy","runmodule.php?module=djail&op=deputy");
		if (is_module_active("sheldon")) addnav("Read the Want Ads","runmodule.php?module=djail&op=wantads");
		$sql = "SELECT ".DB::prefix("module_userprefs").".value, ".DB::prefix("accounts").".name FROM " . DB::prefix("module_userprefs") . "," . DB::prefix("accounts") . " WHERE acctid = userid AND modulename = 'djail' AND setting = 'deputy' AND value > 0";
		$result = DB::query($sql);
		$count = DB::num_rows($result);
		if ($count>0){
			output("You notice the deputy list on the wall:`n`n`b`c`^Deputy List`c`b`0");
			for ($i=0;$i<DB::num_rows($result);$i++){
				$row = DB::fetch_assoc($result);
				output("`c`2- %s `2-`n`c",$row['name']);
			}
		}
	}
}
?>