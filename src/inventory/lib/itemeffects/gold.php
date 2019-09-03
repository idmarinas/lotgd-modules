<?php

/**
 * Increased/Decreased gold of player.
 *
 * @param int   $gold
 * @param array $item Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_increased_gold($gold, $item)
{
    //-- No gold to add/remove
    if (0 == $gold)
    {
        return false;
    }

    global $session;

    $session['user']['gold'] += $gold;

    debuglog("'s gold were altered by $gold by item {$item['itemid']}.");

    $out = [];

    $message = ['`^You `$lose`^ %s gold.`0`n', abs($gold)];
    if ($gold > 0)
    {
        $message = ['`^You `@gain`^ %s gold.`0`n', $gold];
    }

    $out[]= $message;

    return $out;
}
