<?php

/**
 * Increased/Decreased gold of player
 *
 * @param int $gold
 * @param array $item Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_increased_gold($gold, $item)
{
    //-- No gold to add/remove
    if ($gold == 0) { return false; }

    global $session;

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
