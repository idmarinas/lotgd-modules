<?php

require_once 'modules/staminasystem/lib/lib.php';
require_once 'lib/showtabs.php';

popup_header("Your Stamina statistics");

$stamina = get_module_pref("stamina");
$daystamina = 2000000;
$redpoint = get_module_pref("red");
$amberpoint = get_module_pref("amber");
$redpct = get_stamina(0);
$amberpct = get_stamina(1);
$greenpct = get_stamina(2);

if ($greenpct > 0) $color = 'green';
else if ($amberpct > 0)	$color = 'orange';
else $color = 'red';

$text = translate("Total Stamina: %s / %s | Amber point: %s | Red point: %s");
rawoutput("<div class='ui tooltip small progress remove margin $color staminasystem staminabar' data-value='$stamina' data-total='$daystamina'><div class='bar'></div>
	<div class='label'>".sprintf($text,number_format($stamina), number_format($daystamina), number_format($amberpoint), number_format($redpoint))."</div>
</div>");

output("`n`nHere is the nitty-gritty of your Stamina statistics.  The most important value is the total cost, over there on the right.  If there's anything in the Buff column, something's temporarily affecting the cost of performing that action (negative numbers are good!).  More details follow after the stats.`n`n");

$act = get_player_action_list();

$layout = array();
$row = array();
foreach($act as $key => $value)
{
	$class = ($value['class'] != '' ? $value['class'] : 'Other');
	$layout[] = $class;
	$row[$class][$key] = $value;
}

lotgd_showtabs($row, 'show_actions');

$bufflist = unserialize(get_module_pref("buffs", "staminasystem"));

output_notl("`n`n`b".translate_inline("Action Buffs")."`b:`n");

if (is_array($bufflist) && count($bufflist) > 0 && isset($bufflist)){
	$numbuffs = 0;
	foreach ($bufflist AS $key => $vals)
	{
		if ($vals['name'])
		{
			if ($vals['rounds'] > 0) output("`0%s (%s rounds left)`n",$vals['name'],$vals['rounds']);
			else output("`0%s`n",$vals['name']);
			$numbuffs++;
		}
	}
}
else output("None.");

output("`n`nRemember, using the Stamina system is easy - just keep in mind that the more you do something, the better you get at it.  So if you do a lot of the things you enjoy doing the most, the game will let you do more of those things each day.  All of the statistics you see above can help you fine-tune your character, but honestly, 99%% of the time you needn't worry about the statistics and mechanics - they're only there for when you're curious!`n`nAll Bonuses and Penalties are cleared at the start of each game day.`n`n");

popup_footer();

//** Show one item
function show_actions($act)
{
	$action = translate_inline("Action");
	$experience = translate_inline("Experience");
	$cost = translate_inline("Natural Cost");
	$buff = translate_inline("Buff");
	$total = translate_inline("Total");
	$html = "<table class='ui basic very compact unstackable table stamina'><thead><tr><th>$action</th><th>$experience</th><th>$cost</th><th>$buff</th><th>$total</th></tr></thead>";

	ksort($act);
	foreach($act as $key => $values)
	{
		$lvlinfo = stamina_level_up($key);
		$nextlvlexp = round($lvlinfo['nextlvlexp']);
		$nextlvlexpdisplay = number_format($nextlvlexp);
		$currentlvlexp = round($lvlinfo['currentlvlexp']);
		$currentlvlexpdisplay = number_format($currentlvlexp);
		$cost = $values['naturalcost'];
		$level = $values['lvl'];
		$exp = (isset($values['exp']) ? $values['exp'] : 0);
		$mincost = $values['mincost'];
		$costwithbuff = stamina_calculate_buffed_cost($key);
		$modifier = $costwithbuff - $cost;
		$bonus = "None";
		if ($modifier < 0) $bonus = "`@".number_format($modifier)."`0";
		elseif ($modifier > 0) $bonus = "`\$".number_format($modifier)."`0";

		//current exp - current lvl exp / current exp - nextlvlexp

		$html .= "<tr><td class='collapsing'>". sprintf(translate_inline('`^%s`0 Lv %s'), translate_inline($key), $level). "</td><td>";

		if ($values['lvl']<100)
		{
			$expforlvl = $nextlvlexp - $currentlvlexp;
			$expoflvl = $exp - $currentlvlexp;
			$exp = number_format($exp);

			$html .= "<div class='ui tiny indicating progress' data-value='$expoflvl' data-total='$expforlvl'>
				<div class='bar'></div>
				<div class='label'>$exp / $nextlvlexpdisplay</div>
			</div>";
		}
		else $html .= "`4`b".translate_inline("Top Level!")."`b`0";

		$html .= "</td><td>";
		$html .= number_format($cost);
		$html .= "</td><td>";
		$html .= $bonus;
		$html .= "</td><td>";
		$html .= '`Q`b'.number_format($costwithbuff).'`b`0';
		$html .= "</td></tr>";
	}
	$html .= "</table>";

	return appoencode($html, true);
}
