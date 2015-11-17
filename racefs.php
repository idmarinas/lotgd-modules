<?php

function racefs_getmoduleinfo(){
	$info = array(
		"name"=>"Forest Special Race",
		"version"=>"1.1",
		"author"=>"shadowblack",
		"category"=>"Races",
		"download"=>"http://dragonprime.net/users/shadowblack/racefs.zip",
		"vertxtloc"=>"http://dragonprime.net/users/shadowblack/",
      "settings"=>array(
      "Forest Special Race Settings,title",
      "name"=>"Name of this race,text|`#Ice Lizard`0",
      "minedeathchance"=>"Chance for this race to die in the mine,range,0,100,1|15",
      "days"=>"After how many days will the user transform back into their original race?,int|10",
      "city"=>"Home city for this race,location|".getsetting("villagename", LOCATION_FIELDS),
      "max"=>"Maximum number of times a player can become this race during a single DK?,int|2",
      "mindk"=>"Number of DKs required before the player can transform into this race?,int|0",
      "Race Skill and Buffs Settings,title",
      "bname"=>"Name of combat skill,text|`#Frost Breath`0",
      "wname"=>"Name of weaker buff,text|`#Aura of Frost`0",
      "grname"=>"Name of stronger buff,text|`#Aura of Eternal Frost`0",
      "If you change any of these names it is recommended that you change the round and effect messages as well,note",
      ),
      "prefs"=>array(
      "Forest Special Race Preferences,title",
      "age"=>"How many days have passed since the user was transformed?,int|0",
      "uses"=>"How many times can use the `#Racial Skill`0,int|1",
      "times"=>"How many times has been transformed to this race this DK?,int|0",
      "Old Race Info,title",
      "oldrace"=>"Name of the user's old race,viewonly",
      "oldhome"=>"Name of the user's old home city,viewonly",
      ),
	);
	return $info;
}

function racefs_install(){
	module_addeventhook("forest", "return 100;");
	module_addhook("charstats");
	module_addhook("newday");
  module_addhook("dragonkill");
	module_addhook("fightnav-specialties");
	module_addhook("apply-specialties");
  module_addhook("raceminedeath");
  module_addhook("village");
  module_addhook("training-victory");
  module_addhook("changesetting");
	return true;
}

function racefs_uninstall(){
  global $session;
  $race = get_module_setting("name");
  $city = get_module_setting("city");
  $vname = getsetting("villagename", LOCATION_FIELDS);
  $sql = "UPDATE " . db_prefix("accounts") . " SET location='$vname' WHERE location = '$city'";
	db_query($sql);
	if ($session['user']['location'] == $city)
		$session['user']['location'] = $vname;
  $sql = "UPDATE  " . db_prefix("accounts") . " SET race='".RACE_UNKNOWN."' WHERE race='$race'";
  db_query($sql);
	if ($session['user']['race'] == $race)
		$session['user']['race'] = RACE_UNKNOWN;
	return true;
}

function racefs_dohook($hookname,$args){
  global $session,$resline;
  $city = get_module_setting("city");
  $race = get_module_setting("name");
  $bname = get_module_setting("bname");
  $wname = get_module_setting("wname");
  $grname = get_module_setting("grname");
  $maxuses = max(1,round($session['user']['level']/3));

  switch($hookname){

  Case "changesetting":

	if ($args['setting'] == "villagename") {
	if ($args['old'] == get_module_setting("city")) {
		set_module_setting("city", $args['new']);
		}
	}
	break;

  Case "charstats":

  if ($session['user']['race']==$race){
  addcharstat("Vital Info");
  addcharstat("Race", $race);
  addcharstat("Age", get_module_pref("age"));
  }
  break;

  Case "newday":

  if ($session['user']['race'] != $race){
  if (get_module_pref("uses") != 0) set_module_pref("uses",0);
  if (get_module_pref("age") != 0) set_module_pref("age",0);
  if (get_module_pref("oldrace") != "") set_module_pref("oldrace","");
  if (get_module_pref("oldhome") != "") set_module_pref("oldhome","");
  break;
  }
  set_module_pref("age", get_module_pref("age")+1);
  if (get_module_pref("age")>=get_module_setting("days") || $session['user']['dragonkills'] < get_module_setting("mindk")){
  output("With the dawn of a new day you transform back to what you once were.");
  $oldrace = get_module_pref("oldrace");
  $oldhome = get_module_pref("oldhome");
  $session['user']['race'] = $oldrace;
  set_module_pref("homecity",$oldhome,"cities");
  set_module_pref("age",0);
  set_module_pref("uses",0);
  set_module_pref("oldrace","");
  set_module_pref("oldhome","");
  }else{
  racefs_checkcity(); 
  set_module_pref("uses",$maxuses);
  if ($session['user']['level'] >= 10){
  $greateraura = array(
		"name"=>"$grname",
		"rounds"=>-1,
		"allowinpvp"=>1,
		"allowintrain"=>1,
		"badguydefmod"=>0.9,
    "defmod"=>1.15,
    "minioncount"=>1,
		"effectmsg"=>"`#Because of your aura `^{badguy}`# takes `&{damage}`# damage.`0",
		"minbadguydamage"=>$session['user']['level'],
		"maxbadguydamage"=>$session['user']['level']*2,
		"schema"=>"module-fsrace"
		);
	apply_buff("greateraura",$greateraura);
  }elseif($session['user']['level'] >= 5){
  $aura = array(
		"name"=>"$wname",
		"rounds"=>-1,
		"allowinpvp"=>1,
		"allowintrain"=>1,
		"badguydefmod"=>0.9,
    "defmod"=>1.1,
    "roundmsg"=>"`#Your aura ptotects you and weakens `^{badguy}`0",
		"schema"=>"module-fsrace"
		);
	apply_buff("aura",$aura);
  }
  }
  break;

  Case "dragonkill":

  set_module_pref("age",0);
  set_module_pref("uses",0);
  set_module_pref("oldrace","");
  set_module_pref("oldhome","");
  set_module_pref("times",0);
  break;

  Case "fightnav-specialties":

  if ($session['user']['race'] != $race)
    break;
  $uses = get_module_pref("uses");
  $script = $args['script'];
  $fsraceskill = translate_inline("`#Racial Skill");
  if ($uses>0){
  addnav(array("%s (%s points)`0",$fsraceskill,$uses),"");
  addnav(array("%s`7 (%s)`0",$bname,1),$script."op=fight&rs=1",true);
  }
  break;

  Case "apply-specialties":

  $rs = httpget('rs');
  if (get_module_pref("uses") >= $rs && $rs==1){
  apply_buff('rs', array(
		"startmsg"=>"`#You start breathing frost at {badguy}!`0",
		"name"=>"$bname",
		"badguyatkmod"=>0.9,
		"badguydefmod"=>0.9,
		"rounds"=>5,
		"wearoff"=>"`#You stop breathing frost at `^{badguy}`#.`0",
		"minioncount"=>1,
		"effectmsg"=>"`#Your breath attack causes `&{damage}`# damage to `^{badguy}`# and weakens him.`0",
		"minbadguydamage"=>$session['user']['level'],
		"maxbadguydamage"=>$session['user']['level']+$session['user']['dragonkills'],
		"schema"=>"module-fsrace"
					));
	set_module_pref("uses",get_module_pref("uses")-1);
  }
  break;

  Case "raceminedeath":

  if ($session['user']['race'] == $race){
  $args['chance'] = get_module_setting("minedeathchance");
  $args['racesave'] = "Fortunately your skills allow you to get out on time and unharmed.`n";
  }
  break;
  
  Case "village":

  $capital = getsetting("villagename", LOCATION_FIELDS);
  if (is_module_active("cities") && $city==$capital && $session['user']['location']==$city){
  blocknav("forest.php");
  unblocknav("inn.php");
	unblocknav("stables.php");
	unblocknav("rock.php");
	unblocknav("hof.php");
	}
	break;

	Case "training-victory":

  $check1 = round($session['user']['level']/3);
  $check2 = round(($session['user']['level']-1)/3);
  if ($check1>$check2 && $session['user']['level']>4) set_module_pref("uses",get_module_pref("uses")+1);
  }
	return $args;
}

function racefs_runevent($type){
	global $session;
	$race = get_module_setting("name");
  $bname = get_module_setting("bname");
  $wname = get_module_setting("wname");
  $grname = get_module_setting("grname");
  $max = get_module_setting("max");
  $times = get_module_pref("times");

	// I assume this event only shows up in the forest
	$from = "forest.php?";
	$op = httpget('op');
	$session['user']['specialinc'] = "module:racefs";

	Switch ($op){

	Case "":
	Case "search":

  output("As you walk through the forest you suddenly notice a strange `#light`0 through the trees.");
  if ($session['user']['race']==$race){
  output("You remember what happened last time you encountered such a light and decide not to go near it just yet.");
  $session['user']['specialinc'] = "";
  }else{
  output("You wonder if you should investigate, or if it is better to get away while you still can?");
  addnav("Choices");
  addnav("Check out the strange light",$from."op=check");
  addnav("Get away",$from."op=leave");}
  break;

  Case "leave":

  output("You decide it is best to leave the light alone and turn around to go back where you came from.`n");
  $leave = (e_rand(1,22));
  Switch($leave){

  Case 1:
  Case 3:
  Case 4:
  Case 6:
  Case 8:
  Case 14:
  Case 17:

  output("`nYou go on your way without looking back.");
  break;

  Case 2:
  Case 5:
  Case 7:
  Case 12:

  output("`nAs you are about to leave `#a magic missle `0is fired from the strange light, but you manage to dodge it.");
  break;

  Case 13:

  output("`nAs you are about to leave `#a magic missle `0is fired from the strange light.");
  output("It hits you in the back and you feel power flowing through your body!");
  $greateraura = array(
		"name"=>"$grname",
		"rounds"=>30,
		"badguydefmod"=>0.9,
    "defmod"=>1.15,
    "minioncount"=>1,
		"effectmsg"=>"`#Because of your aura `^{badguy}`# takes `&{damage}`# damage.`0",
		"minbadguydamage"=>$session['user']['level'],
		"maxbadguydamage"=>$session['user']['level']*2,
		"schema"=>"module-fsrace"
		);
	apply_buff("greateraura",$greateraura);
  break;

  Case 15:

  output("`nAs you are about to leave `#a magic missle `0is fired from the strange light.");
  output("It hits you in the back and you feel power flowing through your body!");
  $aura = array(
		"name"=>"$wname",
		"rounds"=>60,
		"badguydefmod"=>0.9,
    "defmod"=>1.1,
    "roundmsg"=>"`#Your aura ptotects you and weakens `^{badguy}`0",
		"schema"=>"module-fsrace"
		);
	apply_buff("aura",$aura);
  break;

  Case 9:
  Case 10:
  Case 11:
  Case 16:
  Case 19:
  Case 20:

  output("`nAs you are about to leave `#a magic missle `0is fired from the strange light.");
  output("It hits you in the back, hurting you a little bit and freezing you in place.");
  $freeze = (e_rand(1,5));
  $hploss = floor($freeze*0.1*$session['user']['hitpoints']);
  $session['user']['turns']-=$freeze;
  $session['user']['hitpoints']-=$hploss;
  output("`n`nYou are `#frozen`0! You loose `&%s `0turns and `\$%s `0hit points.",$freeze,$hploss);
  debuglog("Got frozen in the forest and lost $freeze turns and $hploss hit points.");
  break;

  Case 18:

  output("`nAs you are about to leave `#a magic missle `0is fired from the strange light.");
  output("It hits you in the back and slows you down, making you ineffective in combat.");
  $frozen = array(
		"name"=>"`#Frost Curse`0",
		"rounds"=>50,
		"badguyatkmod"=>1.1,
		"badguydefmod"=>1.1,
		"atkmod"=>0.9,
    "defmod"=>0.9,
		"roundmsg"=>"`#The cold slows you down, making you ineffective in combat.`0",
		"schema"=>"module-fsrace"
		);
	apply_buff("frozen",$frozen);
  break;

  Case 21:
  Case 22:

  output("`nThinking it is best not to go anywhere near the light you start walking away from it... fast. After all, who knows what horrible creature or unknown danger is its source?");
  output("You quickly get away without looking back.");
  output("Unknown to you your hasty retreat did not go unnoticed...`n`n");
  output("An old woman gathering mushrooms in the forest sees you leaving.");
  output("The light you saw is coming from `#a magic mushroom`0 that the woman is about to pick.");
  output("\"What a coward! To get scared by a mushroom!\" The woman thinks to herself. As soon as she gets home she tells everyone she knows what she's seen, and not long after that everyone knows you got scared by a mushroom...");
  output("`n`nAshamed of your own cowardice you loose a Charm point.");
  addnews("`%%s`0 was scared by the light from `#a magic mushroom! `0What a coward!",$session['user']['name']);
  $session['user']['charm']--;
  debuglog("Got scared by a strange light in the frest and lost 1 Charm due to cowardice.");
  break;
  }
  $session['user']['specialinc'] = "";
  break;

  Case "check":

  output("You decide to investigate. \"I'm not going to let some light scare me.\" You think to yourself.");
  $check = (e_rand(1,10));
  Switch($check){

  Case 1:

  output("As you start walking towards the light it blinks and vanishes.");
  output("You shrug and go looking for something to kill.");
  break;

  Case 2:

  output("As you start walking towards the light a beam is fired from it.");
  output("Before you can react the beam hits you in the chest and you feel power flowing through your body!");
  $greateraura = array(
		"name"=>"$grname",
		"rounds"=>40,
		"badguydefmod"=>0.9,
    "defmod"=>1.15,
    "minioncount"=>1,
		"effectmsg"=>"`#Because of your aura `^{badguy}`# takes `&{damage}`# damage.`0",
		"minbadguydamage"=>$session['user']['level'],
		"maxbadguydamage"=>$session['user']['level']*2,
		"schema"=>"module-fsrace"
		);
	apply_buff("greateraura",$greateraura);
  break;

  Case 3:

  output("As you start walking towards the light a beam is fired from it.");
  output("Before you can react the beam hits you in the chest and you feel power flowing through your body!");
  $aura = array(
		"name"=>"$wname",
		"rounds"=>80,
		"badguydefmod"=>0.9,
    "defmod"=>1.1,
    "roundmsg"=>"`#Your aura ptotects you and weakens `^{badguy}`0",
		"schema"=>"module-fsrace"
		);
	apply_buff("aura",$aura);
  break;

  Case 4:
  Case 5:
  Case 6:
  Case 7:
  Case 8:
  Case 9:
  Case 10:

  output("You start walking towards the light. As you get closer you see that the source is a `#ball of light`0 floating in the air.");
  output("As you get near it there is a sudden burst of blinding light and a strange feeling passes through you...");
  if (get_module_pref("times") < get_module_setting("max") && $session['user']['dragonkills'] >= get_module_setting("mindk")){
  $oldrace = $session['user']['race'];
  $oldhome = get_module_pref("homecity","cities");
  set_module_pref("oldrace",$oldrace);
  set_module_pref("oldhome",$oldhome);
  $city = get_module_setting("city");
  $session['user']['race'] = $race;
  set_module_pref("homecity",$city,"cities");
  set_module_pref("times",get_module_pref("times")+1);
  output("`n`nYou feel your body changing into something different...");
  output("You have become a %s!",$race);
  output("Your new home is `^%s`0!",$city);
  $maxuses = max(1,round($session['user']['level']/3));
  set_module_pref("uses",$maxuses);
  debuglog("Was transformed into $race.");
  }else{
  output("`n`nYour body changes slightly...");
  output("You feel a little bit tougher.");
  output("You gain `\$1 permanent hitpoint!`0");
  $session['user']['maxhitpoints']++;
  debuglog("Gained 1 max hit point after encountering a strange light in the forest.");
  }
  }
  $session['user']['specialinc'] = "";
  break;
  }
}

function racefs_checkcity(){
  global $session;
	$race = get_module_setting("name");
	$city = get_module_setting("city");

	if ($session['user']['race']==$race && is_module_active("cities")){
		//if they're this race and their home city isn't right, set it up.
		if (get_module_pref("homecity","cities")!=$city){
			set_module_pref("homecity",$city,"cities");
		}
	}
	return true;
}

function racefs_run(){
}
?>
