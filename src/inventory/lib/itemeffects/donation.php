<?php

/**
 * Alter donation points of player.
 *
 * @param int   $donation
 * @param array $item     Data of item
 * @param mixed $points
 *
 * @return array An array of messages
 */
function itemeffects_increased_donation($points, $item)
{
    global $session;

    $session['user']['donation'] += $points;

    debuglog("'s donation points were altered by {$points} by item {$item['id']}.");

    $out = [];

    if ($points > 0)
    {
        $out[] = [
            'item.effect.donation.gain',
            ['points' => $points, 'itemName' => $item['name']],
            'module_inventory',
        ];
    }
    elseif ($points < 0)
    {
        $out[] = [
            'item.effect.donation.lost',
            ['points' => \abs($points), 'itemName' => $item['name']],
            'module_inventory',
        ];
    }

    return $out;
}
