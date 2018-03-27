<?php

/**
 * Increased/Decreased gold of player
 *
 * @param int $gold
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_increased_gold($gold)
{
    //-- No gold to add/remove
    if ($gold == 0) { return false; }

    global $session, $item;

    $session['user']['gold'] += $gold;

    debuglog("'s gold were altered by $gold by item {$item['itemid']}.");

    $out = [];
    if ($gold > 0)
    {
        $out[] = ['`^You `@gain`^ %s gold.`0`n', $gold];
    }
    else
    {
        $out[] = ['`^You `$lose`^ %s gold.`0`n', abs($gold)];
    }

    return $out;
}




if (isset($gold) && $gold != 0) {
			$session['user']['gold'] += $gold;
			debuglog("'s gold were altered by $gold by item {$item['itemid']}.");
			if ($gold > 0) {
				$out[] = sprintf_translate("`^You `@gain`^ %s gold.`n", $gold);
			} else {
				$gold = min(abs($gold), $session['user']['gold']);
				$out[] = sprintf_translate("`^You `\$lose`^ %s gold.`n", $gold);
			}
		}
