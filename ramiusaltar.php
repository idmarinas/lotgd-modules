<?php

/* ver 1.04 by Matt Mullen matt@mattmullen.net */
/* Thanks to Shannon Brown's code to guide in LoGD API */
/* 1 October 2005 */

require_once("lib/http.php");
require_once("lib/villagenav.php");

function ramiusaltar_getmoduleinfo(){
	$info = array(
		"name"=>"Alter to Ramius",
		"version"=>"1.9",
		"author"=>"`7ma`&tt`3@`7matt`&mullen`3.`7net",
		"category"=>"Village",
		"download"=>"http://www.mattmullen.net",
		"settings"=>array(
			"reward = minimumguaranteedreward + random(0;maximumrandombonus),note",
			"ramiusaltarloc"=>"Where does the altar to Ramius appear,location|".getsetting("villagename", LOCATION_FIELDS),
			"sacrificesperday"=>"How many times can the user sacrifice each day,int|1",
			"reward1"=>"What is the minimum guaranteed favor for blood sacrafice,int|10",
			"reward2"=>"What is the minimum guaranteed favor for flesh sacrafice,int|35",
			"reward3"=>"What is the minimum guaranteed favor for spirit sacrafice,int|65",
			"rewardbonus"=>"What is the maximum random bonus for any sacrafice,int|35",
		),
		"prefs"=>array(
			"sacrificedtoday"=>"How many times has the user sacrificed today,int|0",
			"totalgained"    =>"How much total favor has the user gained,int|0",
			"totalsacrifices"=>"How many times has the user ever sacrificed,int|0",
			"totalhploss"    =>"How many total hitpoints has the user lost,int|0",
			"totalturnloss"  =>"How many total turns has the user lost,int|0",
			"totalmaxhploss" =>"How many total max hitpoints has the user lost,int|0"
		)
	);
	return $info;
}

function ramiusaltar_install(){
	module_addhook("changesetting");
	module_addhook("village");
	module_addhook("newday");
	module_addhook("footer-hof");
	return true;
}

function ramiusaltar_uninstall(){
	return true;
}

function ramiusaltar_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "changesetting":
			if ($args['setting'] == "villagename") {		//50
				if ($args['old'] == get_module_setting("ramiusaltarloc")) {
					set_module_setting("ramiusaltarloc", $args['new']);
				}
			}
		break;

		case "village":
			if ($session['user']['location'] == get_module_setting("ramiusaltarloc")) {
				tlschema($args['schemas']['fightnav']);
				addnav($args['fightnav']);
				tlschema();
				addnav("Altar of Ramius","runmodule.php?module=ramiusaltar");
			}
		break;

		case "newday":
			set_module_pref("sacrificedtoday",0);
		break;

		case "footer-hof":
			addnav('Warrior Rankings');
			addnav("Blood & sacrifice","runmodule.php?module=ramiusaltar&op=HOF");
		break;
	}

	return $args;
}

function ramiusaltar_run(){
	global $session;

	$op = httpget("op");
	$type = httpget("type");

	page_header("Altar of Ramius");
	output("`7`c`bAltar of `4Ramius`b`c`n`7");

    addnav('Navigation');
	if ($op==""){

		if (get_module_pref("sacrificedtoday") >= get_module_setting("sacrificesperday") ) {
			output("`nYou are still weak from your previous sacrifice today.  Another visit could do great harm!`n");

			villagenav();
			page_footer();

			return;
		}

		output("`n`2You kneel at the altar of `4Ramius`2, a chill moving through your body.`n");
		output("`n`2You set your jaw and prepare to sacrifice to the overlord of death.");
		output("`n`2Do you:");
//100
		// I know these duplicate the 'addnav' lines, but I like to add my own colors
		output("`n`2 . Give `@B`2lood");
		output("`n`2 . Give `@F`2lesh");
		output("`n`2 . Give `@S`2pirit");
		output("`n`6 . `^D`6efile the altar");

		addnav("B?Give Blood", "runmodule.php?module=ramiusaltar&op=give&type=blood");
		addnav("F?Give Flesh", "runmodule.php?module=ramiusaltar&op=give&type=flesh");
		addnav("S?Give Spirit","runmodule.php?module=ramiusaltar&op=give&type=spirit");
		addnav("D?Defile the altar","runmodule.php?module=ramiusaltar&op=defile");


	} elseif ($op=="give") {

		$gain_favor = e_rand(0,get_module_setting("rewardbonus"));
		$ramius_is_pleased = 1;

		$body_parts = translate_inline(array("arm", "leg", "neck", "torso", "toe", "shoulder", "hip", "ear", "tibiofibular articulation, just between the lateral condyle of the tibia and the fibula"));
		$where_to_cut = e_rand(0, count($body_parts)-1);

		switch ($type) {
			default:
				villagenav();
				page_footer();
				return;
				break;
			case "blood":
				if ($session['user']['hitpoints'] <= $session['user']['maxhitpoints'] * 0.75) {
					output("`n`2You feel too weak to give blood right now, and back away from the altar.");
					debuglog("lost 5 favor trying to give blood with " . $session['user']['hitpoints'] . " of " . $session['user']['maxhitpoints'] . " maxhp left" );
					$ramius_is_pleased = 0;
					break;
				}

				$gain_favor += get_module_setting("reward1");

				set_module_pref("totalhploss", get_module_pref("totalhploss")+$session['user']['hitpoints'] * 0.9);
				$session['user']['hitpoints'] *= 0.1;

				output("`n`7You make a jagged cut on your `Q%s`7 with your `Q%s`7, dripping blood on the altar.`n", $body_parts[$where_to_cut], $session['user']['weapon']);
				output("`n`7The etched foreign symbols on the stone altar begin to glow, and you find yourself chanting the words repeatedly, though you know you've never seen the language.");
				output("`n`7Fear fills you, and you hurry away from this place.  You lose most of your `&health`7.`n");

				debuglog("gained `4" . $gain_favor . " favor`7 giving blood at Altar of Ramius");
				break;
			case "flesh":
                //-- Se modifica para que use la Stamina y no los turnos
                require_once "modules/staminasystem/lib/lib.php";
                $amber = get_stamina();

                // if ($session['user']['turns'] <= 4) {
				if ($amber = get_stamina() < 100)
                {
					output("`n`2You feel too tired to give flesh right now, and back away from the altar.");	//150
					debuglog("lost 5 favor trying to give flesh with " . get_stamina(3) . " turns left");
					$ramius_is_pleased = 0;
					break;
				}

				$gain_favor += get_module_setting("reward2");

				// $turn_loss = e_rand(2,4);
				$stamina = e_rand(2,4)*25000;
				// $session['user']['turns'] -= $turn_loss;
				// set_module_pref("totalturnloss", get_module_pref("totalturnloss")+$turn_loss);
				set_module_pref("totalturnloss", get_module_pref("totalturnloss")+$stamina);
                removestamina($stamina);

				output("`n`7You tear some flesh from your `Q%s`7 with your `Q%s`7.  Gasping in pain, you close your eyes and drop your sacrifice.`n",  $body_parts[$where_to_cut], $session['user']['weapon']);
				output("`n`7The etched foreign symbols on the stone altar begin to glow, and you find yourself chanting the words repeatedly, though you know you've never seen the language.");
				// output("`n`7Fear fills you, and you hurry away from this place.  You lose `@" . $turn_loss . " turn(s)`7.`n");
				output("`n`7Fear fills you, and you hurry away from this place.  You lose `@some stamina`7.`n");

				debuglog("gained `4" . $gain_favor . " favor`7 giving spirit at Altar of Ramius");
				// debuglog("lost `@" . $turn_loss . " turns `7 giving spirit at Altar of Ramius");
				debuglog("lost `@" . $stamina . " stamina `7 giving spirit at Altar of Ramius");
				break;
			case "spirit":
				//-- Compatibilidad con edición IDMarinas >= 0.7.0
				if (0 >= $session['user']['permahitpoints'])
				{
					output("`n`2Your spirit is not strong enough to sacrifice. You back away from the altar.");
					debuglog("lost 5 favor trying to give spirit with " . $session['user']['maxhitpoints'] . " hp at lvl " . $session['user']['level'] . "." );
					$ramius_is_pleased = 0;
					break;
				}

				$gain_favor += get_module_setting("reward3");

				$hp_loss = e_rand(1,3);
				$session['user']['maxhitpoints'] -= $hp_loss;
				$session['user']['hitpoints'] -= $hp_loss;
				set_module_pref("totalmaxhploss", get_module_pref("totalmaxhploss")+$hp_loss);
                //-- Compatibilidad con edición IDMarinas >= 0.7.0
                $session['user']['permahitpoints'] -= $hp_loss;

				output("`n`7You decide to offer to curse your spirit in return for a blessing.`n");
				output("`n`7The etched foreign symbols on the stone altar begin to glow, and you find yourself chanting the words repeatedly, though you know you've never seen the language.");
				output("`n`7Fear fills you, and you hurry away from this place.  You lose `&%s Max HP`7.`n", $hp_loss);

				debuglog("gained `4" . $gain_favor . " favor`7 giving spirit at Altar of Ramius");
				debuglog("lost `&" . $hp_loss . " max hp `7 giving spirit at Altar of Ramius");
				break;
		} // end switch ($type)

		if ($ramius_is_pleased) {
            require_once("modules/alignment/func.php");
            align("-1");

			$session['user']['deathpower'] += $gain_favor;
			set_module_pref("totalgained",get_module_pref("totalgained")+$gain_favor);	//200
			set_module_pref("totalsacrifices",get_module_pref("totalsacrifices")+1);
			set_module_pref("sacrificedtoday", get_module_pref("sacrificedtoday")+1);

			output("`n`n`&You feel `4Ramius`& is pleased.  You gain %s `& favor!", $gain_favor);

		} else {

			addnav("A?Return to the Altar", "runmodule.php?module=ramiusaltar");

			output("`n`4Ramius `7 is displeased!");
			if ($session['user']['deathpower'] < 5) {
				$session['user']['deathpower'] = 0;
			} else {
				$session['user']['deathpower'] -= 5;
			}
		}

	} elseif ($op=="defile") {

		switch (e_rand(1,3)) {
			case 1:
				output("`n`7You kick a clod of loose dirt towards the altar.`n");
				break;
			case 2:
				output("`n`7You aim and spit directly on top of the altar.`n");
				break;
			case 3:
				output("`n`7You swing your %s at the altar, chipping the stone.`n", $session['user']['weapon']);
				break;
		}

		if ($session['user']['deathpower'] > 0) {
			$favor_loss = 50;
			output("`n`4Ramius `7hears of your deed, and is outraged!");
			output("`n`7You lose `4%s favor`7!", min($favor_loss, $session['user']['deathpower']));
			$session['user']['deathpower'] -= min($favor_loss, $session['user']['deathpower']);

            require_once("modules/alignment/func.php");
            align("+2");  // +1 aligment

            require_once "modules/staminasystem/lib/lib.php";
			// $session['user']['turns']++;
            addstamina(25000);
			// output("`n`7Emboldened from your rebuke of the feared `4Ramius`7, you gain `@1 turn`7!");
			output("`n`7Emboldened from your rebuke of the feared `4Ramius`7, you gain `@some stamina`7!");

		} else {
			output("`n`4Ramius `7hears of your deed, but says he's never even heard of %s`7, and ignores you.`n",$session['user']['name']);
		}
	} elseif ($op=="HOF") {

		// we also want in WHERE "AND (locked=0 AND (superuser & SU_HIDE_FROM_LEADERBOARD) = 0)", but that's in DB::prefix("accounts")

//		$sql = "SELECT value FROM ".DB::prefix("module_userprefs")." INNER JOIN ".DB::prefix("accounts")." ON userid=acctid WHERE (locked=0 AND (superuser & ".SU_HIDE_FROM_LEADERBOARD.") = 0)";
//		$sql = "SELECT count(userid) AS c FROM " . DB::prefix("module_userprefs") . " INNER JOIN " . DB::prefix("accounts") . " ON userid=acctid WHERE modulename=\"ramiusaltar\" AND locked=0 AND (superuser & " . SU_HIDE_FROM_LEADERBOARD . ") = 0";
 		$sql = "SELECT count(userid) AS c FROM " . DB::prefix("module_userprefs") . " WHERE modulename=\"ramiusaltar\" ";

		$result = DB::query($sql);
		$row = DB::fetch_assoc($result); 	//250
		$count = $row['c'];


 		output("`c`b`^Sacrifices to the Altar of Ramius`0`b`c`n");
		rawoutput("<table cellspacing='0' cellpadding='2' align='center'>");
		rawoutput("<tr align=center class=\"trhead\">");
		output_notl("<td align=left>`b".translate('Name')."`b</td><td>`b".translate('Favor Gained')."`b</td><td>`b".translate('Sacrifices')."`b</td><td>`b".translate('HP given')."`b</td><td>`b".translate('Turns given')."`b</td><td>`b".translate('Max HP given')."`b</td>", true);
		rawoutput("</tr>");

		$settings = array("sacrificedtoday",
						"totalgained",
						"totalsacrifices",
						"totalhploss",
						"totalturnloss",
						"totalmaxhploss" );

		for($i = 1; $i < $count; $i++){
			$userpref = get_all_module_prefs("ramiusaltar", $i);

			$sql = "SELECT acctid,name FROM " . DB::prefix("accounts") . " WHERE acctid=" . $i . " AND locked=0 AND (superuser & " . SU_HIDE_FROM_LEADERBOARD . ") = 0";
			$result = DB::query($sql);
			if (DB::num_rows($result) < 1) { continue; }
			if ($userpref["totalsacrifices"] < 1) { continue; }
			$row = DB::fetch_assoc($result);

			rawoutput("<tr align=center>");

			for ($j = 0; $j < min(6, count($userpref)); $j++) {

				if ($j == 0) { output_notl("<td align=left>%s</td>", $row['name'], true); continue; }

				output_notl("<td>%s</td>", $userpref[$settings[$j]], true);

			}
			rawoutput("</tr>");
		}
		rawoutput("</table>");

		addnav("Back to HOF","hof.php");

	}

    addnav('Return');
	villagenav();
	page_footer();

}

?>
