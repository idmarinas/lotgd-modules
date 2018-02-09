<?php

function staminahof_getmoduleinfo(){
	$info=array(
		"name"=>"Stamina System - HOF",
		"version"=>"2009-08-12",
		"author"=>"Dan Hall, aka Caveman Joe, improbableisland.com",
		"category"=>"Stamina",
		"download"=>"",
	);
	return $info;
}

function staminahof_install(){
	module_addhook("footer-hof");
	return true;
}

function staminahof_uninstall(){
	return true;
}

function staminahof_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "footer-hof":
			addnav("Warrior Rankings");
			addnav("Action Level Rankings","runmodule.php?module=staminahof");
		break;
	}
	return $args;
}

function staminahof_run()
{
	global $session;

	page_header("Action Level Rankings");

	require_once "modules/staminasystem/lib/lib.php";

	addnav("Exit");
	addnav("Return to the HOF","hof.php");
	$actions = get_default_action_list();

	// Output navs to each action
	foreach($actions AS $action => $vals)
	{
		// rawurlencode
		addnav('Actions');
		addnav($action, "runmodule.php?module=staminahof&action=".urlencode($action)."&skip=0");
	}


	// Now show the HOF
	$hof = httpget('action');
	if ($hof)
	{
		// Get action for each user
		$hofpage = [];
		$pages = 0;
		$numres=0;

		$namessql = "SELECT acctid, name FROM " . DB::prefix("accounts") . "";
		$namesresult = DB::query($namessql);

		$staminasql = "SELECT setting,value,userid FROM ".DB::prefix("module_userprefs")." WHERE modulename='staminasystem' AND setting='actions'";
		$staminaresult = DB::query($staminasql);

		$scount = DB::num_rows($staminaresult);

		for ($i=0;$i<$scount;$i++){
			$row = DB::fetch_assoc($staminaresult);
			$actions_array = @unserialize($row['value']);
			if (isset($actions_array[$hof]))
			{
				$actiondetails = $actions_array[$hof];
				$hofpage[$row['userid']]['exp'] = isset($actiondetails['exp']) ? $actiondetails['exp'] : 0;
				$hofpage[$row['userid']]['lvl'] = isset($actiondetails['lvl']) ? $actiondetails['lvl'] : 0;
			}
			else
			{
				$hofpage[$row['userid']]['exp'] = 0;
				$hofpage[$row['userid']]['lvl'] = 0;
			}
		}

		$ncount = DB::num_rows($namesresult);

		for ($i=0;$i<$ncount;$i++){
			$row = DB::fetch_assoc($namesresult);
			$numres++;
			$hofpage[$row['acctid']]['name'] = $row['name'];
			//sorting this later on will overwrite the order, so we save it here
			$hofpage[$row['acctid']]['acctid'] = $row['acctid'];
		}

		$pages = ceil($numres/50);

		for ($i=0; $i<=$pages; $i++){
			addnav("Pages");
			$page=$i*50;
			addnav(array("Page %s",$i+1),"runmodule.php?module=staminahof&action=$hof&skip=$page");
		}

		usort($hofpage,"staminahof_sort");

		$dexp = translate_inline("Experience");
		$dname = translate_inline("Name");
		$dlvl = translate_inline("Level");
		$drnk = translate_inline("Rank");
		output_notl("`n`b`c`@".translate_inline($hof)."`n`n`c`b");
		rawoutput("<table class='ui very compact striped table'>");
		rawoutput("<thead><tr><th>$dname</th><th>$drnk</th><th>$dlvl</th><th>$dexp</th></tr></thead>");

		for($i = httpget('skip') ; $i <= httpget('skip')+50; $i++)
		{
			if (isset($hofpage[$i]) && $hofpage[$i])
			{
				rawoutput("<tr class='".($hofpage[$i]['name']==$session['user']['name'] ? 'active' : '')."'><td>");
				output_notl("%s",$hofpage[$i]['name']);
				rawoutput("</td><td>");
				output_notl("%s",$i+1);
				rawoutput("</td><td>");
				output_notl("%s",$hofpage[$i]['lvl']);
				rawoutput("</td><td>");
				output_notl("%s",number_format($hofpage[$i]['exp']));
				rawoutput("</td></tr>");
			}
		}
		rawoutput("</table>");
	} else {
		output("`0What would you like to see?");
	}
	page_footer();
	return true;
}

function staminahof_sort($x, $y){
	if ($x['exp'] == $y['exp']) return 0;
	else if ($x['exp'] < $y['exp']) return 1;
	else return -1;
}
