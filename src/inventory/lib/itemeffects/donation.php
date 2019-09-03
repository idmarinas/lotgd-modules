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
    global $session;

    $session['user']['donation'] += $points;

    debuglog("'s donation points were altered by $points by item {$item['itemid']}.");

    $out = [];

    if ($points > 0)
    {
        $out[] = ['`^You `@gain`0 %s donation %s.`0`n', $points, \LotgdFormat::pluralize($points, 'point', 'points')];

        return $out;
    }
    elseif ($points < 0)
    {
        $out[] = ['`^You `$lose`0 %s donation %s.`0`n', abs($points), \LotgdFormat::pluralize(abs($points), 'point', 'points')];

        return $out;
    }

    return false;
}
