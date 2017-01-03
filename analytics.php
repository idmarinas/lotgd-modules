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

	$players = array();

	//get current accounts, put into an array
	$cursql = "SELECT regdate, donation FROM " . DB::prefix("accounts") . "";
	$curresult = DB::query($cursql);
	for ($i=0;$i<DB::num_rows($curresult);$i++){
		$currow = DB::fetch_assoc($curresult);
		$trimdate = substr($currow['regdate'],0,10);
		$players[$trimdate]['stillalive']['characters'] += 1;
		$players[$trimdate]['stillalive']['donation'] += $currow['donation'];
	}

	//get expired accounts, put into an array
	$expsql = "SELECT * FROM " . DB::prefix("expchars") . "";
	$expresult = DB::query($expsql);
	for ($i=0;$i<DB::num_rows($expresult);$i++){
		$exprow = DB::fetch_assoc($expresult);
		$trimdate = substr($exprow['datecreated'],0,10);
		$players[$trimdate]['dead']['characters'] += 1;
		$players[$trimdate]['dead']['donation'] += $exprow['donation'];
	}

	krsort($players);

	rawoutput("<table border='0' cellpadding='2' cellspacing='2'>");
	rawoutput("<tr><td><b>Date</b></td><td><b>Characters Created</b></td><td><b>Characters Still Active</b></td><td><b>Retention Rate</b></td><td><b>Donation</b></td></tr>");

	$class="trlight";

	foreach($players as $date=>$vals){
		if ($date != "0000-00-00"){
			$class=(date("W",strtotime($date))%2?"trlight":"trdark");
			$totalcreated = $vals['stillalive']['characters'] + $vals['dead']['characters'];
			$retention = round(($vals['stillalive']['characters']/$totalcreated)*100);
			$donation = $vals['stillalive']['donation'] + $vals['dead']['donation'];
			rawoutput("<tr class='$class'><td><b>{$date}</b></td><td align='left'><img src='images/trans.gif' width='{$totalcreated}' border='1' height='5'>{$totalcreated}</td><td align='left'><img src='images/trans.gif' width='{$vals['stillalive']['characters']}' border='1' height='5'>{$vals['stillalive']['characters']}</td><td align='left'><img src='images/trans.gif' width='{$retention}' border='1' height='5'>{$retention}</td><td align='center'>{$donation}</td></tr>");
		}
	}
	rawoutput("</table>");

	// debug("expirations");
	// debug($expirations);

	// rawoutput("<table border='0' cellpadding='2' cellspacing='2'>");
	// $class="trlight";
	// $odate=date("Y-m-d");
	// $today=date("Y-m-d");
	// $j=0;
	// $cumul = 0;
	// $number=DB::num_rows($result);
	// for ($i=0;$i<$number;$i++){
		// $row = DB::fetch_assoc($result);
		// $diff = (strtotime($odate)-strtotime($row['d']))/86400;
		// $class=(date("W",strtotime($row['d']))%2?"trlight":"trdark");
		// $cumul+=$row['c'];
		// debug($row['d']);
		// $odate = $row['d'];
		// rawoutput("<tr class='$class'><td>{$row['d']}</td><td><img src='images/trans.gif' width='{$row['c']}' border='1' height='5'>{$row['c']}</td><td>{$expirations[$odate]}</td><td align='right'>{$row['donation']}</td></tr>");
	// }
	// rawoutput("</table>");

	page_footer();
}

?>