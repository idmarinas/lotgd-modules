<?php

function mechanicalturk_getmoduleinfo(){
	$info = array(
		"name"=>"Mechanical Turk",
		"author"=>"Dan Hall",
		"version"=>"2008-09-24",
		"category"=>"Administrative",
		"download"=>"",
		"settings"=>array(
			"Mechanical Turk Module Settings,title",
			"addpoints"=>"How many Donator Points will be awarded when the player's monster is accepted?,int|50",
		),
	);

	return $info;
}

function mechanicalturk_install(){
	$mechanicalturk = array(
		'creatureid'=>array('name'=>'creatureid', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
		'creaturename'=>array('name'=>'creaturename', 'type'=>'varchar(50)'),
		'creaturecategory'=>array('name'=>'creaturecategory', 'type'=>'varchar(50)'),
		'creatureweapon'=>array('name'=>'creatureweapon', 'type'=>'varchar(50)'),
		'creaturewin'=>array('name'=>'creaturewin', 'type'=>'varchar(120)'),
		'creaturelose'=>array('name'=>'creaturelose', 'type'=>'varchar(120)'),
		'creaturelevel'=>array('name'=>'creaturelevel', 'default'=>'0', 'type'=>'int(11) unsigned'),
		'forest'=>array('name'=>'forest', 'default'=>'0', 'type'=>'int(11) unsigned'),
		'graveyard'=>array('name'=>'graveyard', 'default'=>'0', 'type'=>'int(11) unsigned'),
		'description'=>array('name'=>'description', 'type'=>'text'),
		'submittedby'=>array('name'=>'submittedby', 'type'=>'varchar(50)'),
		'uid'=>array('name'=>'uid', 'type'=>'int(11) unsigned'),
		'key-PRIMARY'=>array('name'=>'PRIMARY', 'type'=>'primary key',	'unique'=>'1', 'columns'=>'creatureid'),
	);
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix('mechanicalturk'), $mechanicalturk, true);
	module_addhook("forest");
	module_addhook("superuser");
	return true;
}

function mechanicalturk_uninstall(){
	$sql = 'DROP TABLE IF EXISTS '.db_prefix( 'mechanicalturk' );
	db_query( $sql );
	return true;
}

function mechanicalturk_dohook($hookname,$args){
	global $session, $enemies;
	switch($hookname){
	case "forest":
		addnav("Other Actions");
		addnav("Report a monster sighting","runmodule.php?module=mechanicalturk&creatureaction=report");
		break;
	case "superuser":
		addnav("Show list of submitted monsters","runmodule.php?module=mechanicalturk&creatureaction=showsubmitted");
		break;
	}
	return $args;
}

function mechanicalturk_run(){
	global $session;
	require_once("common.php");
	require_once("lib/http.php");
	page_header("Report a Monster Sighting");
	$points = get_module_setting("addpoints");
	switch (httpget("creatureaction")){
		case "report":
			require_once("lib/showform.php");
			// $level = 1;
			output('You head into a little hut on the outskirts of the Outpost, close to where the clearing turns to jungle.`n`n');
			output('An excitable-looking man sits behind a desk, a pair of binoculars slung around his neck.`n`n');
			output('"Hello!" he calls to you, practically bouncing with geeky excitement. "Have you come to report a sighting of a new monster? I do so enjoy writing them up!" He opens a little ledger and whips out a pen, ready for your report.`n`n');
			output('You\'ve heard rumours about this guy. Billing himself as a monster expert, he listens to the reports of new monsters that players find, and writes them down in his book. Sauntering into his hut and providing deadly serious reports of completely made-up monsters is a game that many contestants enjoy playing. However, given the aura of Improbability surrounding his innocent-looking ledger, the rumours contain a dark side as well; sometimes, if a made-up monster is deemed to be Improbable enough by the standards of whatever strange powers control the Island, it takes on a physical form in the Jungle...`n`n');
			output("As you're thinking this, a splintering CRUNCH from your left causes you to jump three feet into the air. `%Admin `4Caveman`\$Joe`0 is standing in the remains of the fourth wall of the hut, holding a large axe. He strikes an attractive pose and says \"`4This is Improbable Island's monster submission hut. Think of a new monster, and submit it here. If your idea is accepted, you'll get %s Donator Points!`0\"`n`n",$points);
			output("He turns to the little man sat behind the desk, who at this moment is picking bits of wood out of his tea, hair and person in general. `%Admin `4Caveman`\$Joe`0 shows him a smile. \"`4I'd say 'Sorry about your wall,' mate, but I'm not. All part of the job, you see.`0\"`n`n");
			output('With that, he walks back out of the hole in the fourth wall, attaches his Admin Goggles, leaps into the sky and flies away.`n`n');
			output('"What a very, very strange man," says the little man behind the desk. Quietly.`n`n');
			$text = sprintf_translate("When writing descriptions, please use %s to go down one line, and %s to leave a blank line. That's %s, not %s. The %s key is usually in the top left corner of your keyboard - it's the same key you use for colour codes.", '`n', '`n`n', '`n',"'n",'`');
			rawoutput($text);

			$sql = "SELECT DISTINCT `creaturecategory` FROM `creatures` WHERE `creaturecategory` != ''";
			$result = db_query($sql);
			$enum = ',Ninguna';
			while($row = db_fetch_assoc($result)) $enum .= ','.$row['creaturecategory'].','.$row['creaturecategory'];

			$form = array(
				"Creature Properties,title",
				"creaturename"=>"Creature Name",
				"creaturecategory"=>"Creature Category,enum,".$enum,
				"creatureweapon"=>"Weapon",
				"creaturewin"=>"Win Message (Displayed when the creature kills the player)",
				"creaturelose"=>"Death Message (Displayed when the creature is killed by the player)",
				// 18 to make a non-forest available monster
				// (ie, graveyard only)_
				"creaturelevel"=>"Level,range,1,18,1",
				"forest"=>"Creature is in Jungle?,bool",
				"graveyard"=>"Creature is on FailBoat?,bool",
				"description"=>"A long description of the creature,textarea",
			);
			$row = array("creatureid"=>0);
			addnav('I honestly don\'t have any ideas.');
			addnav('Back to the Jungle','forest.php');
			rawoutput("<form action='runmodule.php?module=mechanicalturk&creatureaction=save' method='POST'>");
			showform($form, $row);
			rawoutput("</form>");
			addnav("", "runmodule.php?module=mechanicalturk&creatureaction=save");
			break;
		case "save":
			$post = httpallpost();
			$post['submittedby'] = $session['user']['name'];
			$post['uid'] = $session['user']['acctid'];
			unset($post['creatureid'], $post['showFormTabIndex']);

			$sql = "INSERT INTO ".db_prefix("mechanicalturk")."(".implode(',',array_keys($post)).") VALUES ('".implode("','",$post)."')";
			db_query( $sql );
			debug ($sql);
			output("`4The monster \"`^%s`4\" has been submitted.`n`nDue to the high volume of monster submissions, it may take several days or even weeks before you hear back from us. Please be patient!`0`n`nThe little man behind the desk looks around, confused. \"Who said that?!\"`n`nYou decide it'd be best to get out of here.", $post['creaturename'] );
			addnav("Back to the Jungle","forest.php");
			break;
		case "showsubmitted":
			$sql = "SELECT creatureid,creaturename,creatureweapon,creaturewin,creaturelose,creaturelevel,forest,graveyard,description,submittedby,uid FROM " . db_prefix("mechanicalturk");
			$result = db_query($sql);
			for ($i=0;$i<db_num_rows($result);$i++){
				$row=db_fetch_assoc($result);
				output("Monster submission by %s`n",$row['submittedby']);
				output_notl("%s`n",$row['description']);
				output("You have encountered %s which lunges at you with %s!`n",$row['creaturename'],$row['creatureweapon']);
				rawoutput("<a href=\"runmodule.php?module=mechanicalturk&creatureaction=edit&id=".$row['creatureid']."\">Edit this monster</a> | <a href=\"runmodule.php?module=mechanicalturk&creatureaction=reject&id=".$row['creatureid']."\">Reject this monster</a> | <a href=\"runmodule.php?module=mechanicalturk&creatureaction=accept&id=".$row['creatureid']."\">Accept this monster</a>");
				addnav("", "runmodule.php?module=mechanicalturk&creatureaction=edit&id=".$row['creatureid']);
				addnav("", "runmodule.php?module=mechanicalturk&creatureaction=reject&id=".$row['creatureid']);
				addnav("", "runmodule.php?module=mechanicalturk&creatureaction=accept&id=".$row['creatureid']);
				output("`n`n");
			}
			addnav("Back to the Superuser grotto","superuser.php");
			break;
		case "edit":
			$id=httpget("id");
			require_once("lib/showform.php");
			addnav("Back to the Jungle","forest.php");
			$form = array(
				"Creature Properties,title",
				"creatureid"=>"Creature id,hidden",
				"creaturename"=>"Creature Name",
				"creaturecategory"=>"Creature Category",
				"creatureweapon"=>"Weapon",
				"creaturewin"=>"Win Message (Displayed when the creature kills the player)",
				"creaturelose"=>"Death Message (Displayed when the creature is killed by the player)",
				// 18 to make a non-forest available monster
				// (ie, graveyard only)_
				"creaturelevel"=>"Level,range,1,18,1",
				"forest"=>"Creature is in Jungle?,bool",
				"graveyard"=>"Creature is on FailBoat?,bool",
				"description"=>"A long description of the creature,textarea",
			);
			$sql = "SELECT creatureid,creaturename,creatureweapon,creaturewin,creaturelose,creaturelevel,forest,graveyard,description,submittedby,uid FROM " . db_prefix("mechanicalturk") . " WHERE creatureid = $id";
			$result = db_query($sql);
			$row=db_fetch_assoc($result);
			debug ($row);
			rawoutput("<form action='runmodule.php?module=mechanicalturk&creatureaction=update' method='POST'>");
			showform($form, $row);
			rawoutput("</form>");
			addnav("", "runmodule.php?module=mechanicalturk&creatureaction=update");
			addnav("Back to the submission list","runmodule.php?module=mechanicalturk&creatureaction=showsubmitted");
			addnav("Back to the Superuser grotto","superuser.php");
			break;
		case "update":
			addnav("Back to the submission list","runmodule.php?module=mechanicalturk&creatureaction=showsubmitted");
			addnav("Back to the Superuser grotto","superuser.php");
			$creatureid = httppost('creatureid');
			$creaturename = httppost('creaturename');
			$creaturecategory = httppost('creaturecategory');
			$creatureweapon = httppost('creatureweapon');
			$creaturewin = httppost('creaturewin');
			$creaturelose = httppost('creaturelose');
			$creaturelevel = httppost('creaturelevel');
			$forest = httppost('forest');
			$graveyard = httppost('graveyard');
			$description = httppost('description');
			$sql="UPDATE " . db_prefix("mechanicalturk") . " SET creaturename = '$creaturename', creaturecategory = '$creaturecategory', creatureweapon = '$creatureweapon', creaturewin = '$creaturewin', creaturelose = '$creaturelose', creaturelevel = '$creaturelevel', forest = $forest, graveyard = $graveyard, description = '$description' WHERE creatureid = $creatureid";
			db_query( $sql );
			debug ($sql);
			output("All done!");
			break;
		case "reject":
			$id=httpget("id");
			$sql = "SELECT creatureid,creaturename,creatureweapon,creaturewin,creaturelose,creaturelevel,forest,graveyard,description,submittedby,uid FROM " . db_prefix("mechanicalturk") . " WHERE creatureid = $id";
			$result = db_query($sql);
			$row=db_fetch_assoc($result);
			$message = translate_mail(array('It\'s not good news to hear, but I\'m afraid your monster idea (the one named %s) just wasn\'t what we were looking for. Please feel free to try again, though!',$row['creaturename']));
			require_once("lib/systemmail.php");
			systemmail($row['uid'],translate_inline("Your monster has been rejected!"),$message);
			$sql = "DELETE FROM " . db_prefix("mechanicalturk") . " WHERE creatureid = '$id'";
			db_query( $sql );
			output("The monster has been deleted, and the author notified.");
			addnav("Show list of submitted monsters","runmodule.php?module=mechanicalturk&creatureaction=showsubmitted");
			break;
		case "accept":
			$id=httpget("id");
			$sql = "SELECT creaturename,creaturecategory,creatureweapon,creaturewin,creaturelose,creaturelevel,forest,graveyard,description,submittedby,uid FROM " . db_prefix("mechanicalturk") . " WHERE creatureid = $id";
			$result = db_query($sql);
			$row=db_fetch_assoc($result);
			debug ($row);
			output("Sending this to creatures.php.");
			require_once("lib/showform.php");
			require_once("lib/listfiles.php");
			$sort = list_files("creatureai", array());
			sort($sort);
			$scriptenum=implode("",$sort);
			$scriptenum=",,none".$scriptenum;
			$form = array(
				"Creature Properties,title",
				"creatureid"=>"Creature id,hidden",
				"creaturename"=>"Creature Name",
				"creaturecategory"=>"Creature Category",
				"creatureweapon"=>"Weapon",
				"creaturewin"=>"Win Message (Displayed when the creature kills the player)",
				"creaturelose"=>"Death Message (Displayed when the creature is killed by the player)",
				// 18 to make a non-forest available monster
				// (ie, graveyard only)_
				"creaturelevel"=>"Level,range,1,18,1",
				"forest"=>"Creature is in forest?,bool",
				"graveyard"=>"Creature is in graveyard?,bool",
				"creatureaiscript"=>"Creature's A.I.,enum".$scriptenum,
			);
			rawoutput("<form action='creatures.php?op=save' method='POST'>");
			showform($form, $row);
			rawoutput("</form>");
			output("Monster description:`n`n");
			rawoutput("".$row['description']."");
			output("`n`n`bCOPY THIS TO YOUR CLIPBOARD NOW.`b The Description is not automated and must be input manually.");
			$message = translate_mail(array('Congratulations! Your monster idea (the one named %s) has been accepted! Your Donator Points have also been applied to your account. Enjoy them!',$row['creaturename']));
			require_once("lib/systemmail.php");
			systemmail($row['uid'],translate_inline("Your monster idea has been accepted!"),$message);
			addnav("","creatures.php?op=save");
			$acctid = $row['uid'];
			addnav("Go back to the list of submitted monsters","runmodule.php?module=mechanicalturk&creatureaction=showsubmitted");
			$sql="UPDATE ".db_prefix("accounts")." SET donation=donation+$points WHERE acctid=$acctid";
			db_query($sql);
			debuglog("Add $points donation points as rewards for creature submit.", false, $acctid, 'mechanicalturk');
			$sql = "DELETE FROM " . db_prefix("mechanicalturk") . " WHERE creatureid = '$id'";
			db_query($sql);
			break;
	}
	page_footer();
}
?>