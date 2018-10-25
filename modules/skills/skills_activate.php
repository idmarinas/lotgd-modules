<?php
	$l = httpget('l'); //type of attack
	$sql = "SELECT * FROM ".DB::prefix("skills")." WHERE skillid = $l";
	$result = DB::query($sql);
	$skill = DB::fetch_assoc($result);
	$ccode = $skill['ccode'];
	set_module_pref("cooldown",$skill['cooldown']);
	eval($skill['execvalue']);
	$buffs = unserialize($skill['buffids']);
	require_once("modules/skills/skills_func.php");
	foreach ($buffs as $buffid => $Xactive){
		$buff = get_skills_buff($buffid,$ccode);
		apply_buff("skills-$buffid",$buff);
	}
?>
