<?php
	$script = $args['script'];

	$sql = "SELECT * FROM ".DB::prefix("skills");
	$result = DB::query_cached($sql, "skills-navs");
	$first = false;
	$number = DB::num_rows($result);
	$cooldown = get_module_pref("cooldown");
	debug('cooldown '.$cooldown);
	debug($number);
	if (get_module_pref("active") == 1) {
		if ($cooldown > 0) {
			$colorarray = array(1=>"`@",2=>"`2",3=>"`^",4=>"`6",5=>"`q",6=>"`Q",7=>"`$",8=>"`4",9=>"`%",10=>"`5",11=>"`5",12=>"`5",13=>"`5",14=>"`5",15=>"`5",'15+'=>'`)');
			if ($cooldown > 15) $cooldown = "15+";
			addnav(array("`&Misc Skills (Cooldown: %s%s Rds`&)`0",$colorarray[$cooldown],$cooldown));
			for ($i=0;$i<$number;$i++) {
				$row=DB::fetch_assoc($result);
				if (eval("return ".$row['requirement'].";")) {
					addnav_notl(array("`) %s`0",$row['name']),'');
				}
			}
		} else {
			for ($i=0;$i<$number;$i++) {
				$row=DB::fetch_assoc($result);
				eval($row['globals']);

				addnav("`&Misc Skills (Cooldown: `#Ready`&)`0");
				if (eval("return ".$row['requirement'].";")) {
					addnav_notl(array("%s %s`0",$row['ccode'],$row['name']),$script."op=fight&skill=$spec&l={$row['skillid']}");
				}
			}
		}
	}
?>