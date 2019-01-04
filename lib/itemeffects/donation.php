<?php

/**
 * Increased/Decreased donation points of player.
 *
 * @param int   $donation
 * @param array $item     Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_increased_donation($points, $item)
{
    //-- No points to add/remove
    if (0 == $points)
    {
        return false;
    }

    global $session;

    $session['user']['donation'] += $points;

    debuglog("'s donation points were altered by $points by item {$item['itemid']}.");

    $out = [];

    if ($points > 0)
    {
        if (1 == $points)
        {
            $out[] = '`^You `@gain`^ one donation point.`0`n';
        }
        else
        {
            $out[] = ['`^You `@gain`^ %s donation points.`0`n', $points];
        }
    }
    else
    {
        if (-1 == $points)
        {
            $out[] = '`^You `$lose`^ one donation point.`0`n';
        }
        else
        {
            $out[] = ['`^You `$lose`^ %s donation points.`0`n', abs($points)];
        }
    }

    return $out;
}
