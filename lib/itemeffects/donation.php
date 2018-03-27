<?php

/**
 * Increased/Decreased donation points of player
 *
 * @param int $donation
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_increased_donation($points)
{
    //-- No points to add/remove
    if ($points == 0) { return false; }

    global $session, $item;

    $session['user']['donation'] += $points;

    debuglog("'s donation points were altered by $points by item {$item['itemid']}.");

    $out = [];
    if ($points > 0)
    {
        if($points == 1) { $out[] = '`^You `@gain`^ one donation point.`0`n'; }
        else { $out[] = ['`^You `@gain`^ %s donation points.`0`n', $points]; }
    }
    else
    {
        if($points == -1) { $out[] = '`^You `$lose`^ one donation point.`0`n'; }
        else { $out[] = ['`^You `$lose`^ %s donation points.`0`n', abs($points)]; }
    }

    return $out;
}





