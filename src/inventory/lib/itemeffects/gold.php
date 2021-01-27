<?php

/**
 * Increased/Decreased gold of player.
 *
 * @param int   $gold
 * @param array $item Data of item
 *
 * @return array An array of messages
 */
function itemeffects_increased_gold($gold, $item)
{
    //-- No gold to add/remove
    if (0 == $gold)
    {
        return [];
    }

    global $session;

    $session['user']['gold'] += $gold;

    debuglog("'s gold were altered by {$gold} by item {$item['id']}.");

    $out = [];

    if ($gold > 0)
    {
        $out[] = ['item.effect.gold.gain',
            ['gold' => $gold, 'itemName' => $item['name']],
            'module_inventory',
        ];
    }
    else
    {
        $out[] = ['item.effect.gold.lost',
            ['gold' => \abs($gold), 'itemName' => $item['name']],
            'module_inventory',
        ];
    }

    return $out;
}
