<?php

function analytics_getmoduleinfo(){
	$info = array(
		"name"=>"Analytics",
		"category"=>"Administrative",
		"version"=>"2008-07-28",
		"author"=>"Dan Hall, and anyone who wants to join in",
		"download"=>"http://dragonprime.net/index.php?topic=10080.0",
	);
	return $info;
}

function analytics_install(){
	$expchars = array(
		'acctid'=>array('name'=>'acctid', 'type'=>'int(11) unsigned'),
		'dateexpired'=>array('name'=>'dateexpired', 'type'=>'datetime'),
		'datecreated'=>array('name'=>'datecreated', 'type'=>'datetime'),
		'donation'=>array('name'=>'donation', 'default'=>'0', 'type'=>'int(11) unsigned'),
		'key-PRIMARY'=>array('name'=>'PRIMARY', 'type'=>'primary key',	'unique'=>'1', 'columns'=>'acctid'),
	);
	require_once("lib/tabledescriptor.php");
	synctable(DB::prefix('expchars'), $expchars, true);

	module_addhook("superuser");
	module_addhook("delete_character");
	return true;
}

function analytics_uninstall(){
	return true;
}

function analytics_dohook($hookname,$args){
	switch($hookname){
	case "superuser":
		addnav("Analytics","runmodule.php?module=analytics");
		break;
	case "delete_character":
		$sql = "SELECT * FROM ".DB::prefix("accounts")." WHERE acctid='{$args['acctid']}'";
		$result = DB::query($sql);
		if (DB::num_rows($result) > 0){
			$row = DB::fetch_assoc($result);
			debug($row);
			$sql = "INSERT INTO ".DB::prefix("expchars")." (acctid,dateexpired,datecreated,donation) VALUES ('" . $row['acctid'] . "','" . date("Y-m-d") . "','" . $row['regdate'] . "','" . $row['donation'] . "')";
			debug($sql);
			DB::query($sql);
		}
		break;
	}
	return $args;
}

function analytics_run(){
	global $session;
	addnav("Back to the Grotto","superuser.php");
	addnav("Refresh","runmodule.php?module=analytics");

	page_header("Analytics!");

	$players = [];

	//get current accounts, put into an array
	$cursql = "SELECT regdate, donation FROM " . DB::prefix("accounts") . "";
    $curresult = DB::query($cursql);
    while($currow = DB::fetch_assoc($curresult))
    {
        $trimdate = substr($currow['regdate'],0,10);
        if (isset($players[$trimdate]['stillalive']))
        {
            $players[$trimdate]['stillalive']['characters'] += 1;
            $players[$trimdate]['stillalive']['donation'] += $currow['donation'];
        }
        else
        {
            $players[$trimdate]['stillalive']['characters'] = 1;
            $players[$trimdate]['stillalive']['donation'] = $currow['donation'];
        }
    }

	//get expired accounts, put into an array
	$expsql = "SELECT * FROM " . DB::prefix('expchars') . "";
    $expresult = DB::query($expsql);
    while($exprow = DB::fetch_assoc($expresult))
    {
        $trimdate = substr($exprow['datecreated'],0,10);
        if (isset($players[$trimdate]['dead']))
        {
            $players[$trimdate]['dead']['characters'] += 1;
            $players[$trimdate]['dead']['donation'] += $exprow['donation'];
        }
        else
        {
            $players[$trimdate]['dead']['characters'] = 1;
            $players[$trimdate]['dead']['donation'] = $exprow['donation'];
        }
    }

	krsort($players);

	rawoutput("<table class='ui very compact striped selectable table'>");
	rawoutput("<thead><tr><th><b>Date</b></th><th><b>Characters Created</b></th><th><b>Characters Still Active</b></th><th><b>Retention Rate</b></th><th><b>Donation</b></th></tr></thead><tbody>");

    foreach($players as $date=>$vals)
    {
        if ($date != '0000-00-00')
        {
            $stillalive = isset($vals['stillalive']['characters']) ? $vals['stillalive']['characters'] : 0;
            $dead = isset($vals['dead']['characters']) ? $vals['dead']['characters'] : 0;
			$totalcreated = $stillalive + $dead;
            $retention = round(($stillalive/$totalcreated)*100);
            $stillaliveDonation = isset($vals['stillalive']['donation']) ? $vals['stillalive']['donation'] : 0;
            $deadDonation = isset($vals['dead']['donation']) ? $vals['dead']['donation'] : 0;
			$donation = $stillaliveDonation + $deadDonation;
			rawoutput("<tr><td><b>{$date}</b></td><td align='left'><img src='images/trans.gif' width='{$totalcreated}' border='1' height='5'>{$totalcreated}</td><td align='left'><img src='images/trans.gif' width='{$stillalive}' border='1' height='5'>{$stillalive}</td><td align='left'><img src='images/trans.gif' width='{$retention}' border='1' height='5'>{$retention}</td><td align='center'>{$donation}</td></tr>");
		}
	}
	rawoutput("</tbody></table>");

	page_footer();
}
