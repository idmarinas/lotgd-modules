<?php

/**
 * Alter gems of player.
 *
 * @param int   $gems
 * @param array $item Data of item
 *
 * @return array An array of messages
 */
function itemeffects_increased_gems($gems, $item): array
{
    global $session;

    $session['user']['gems'] += $gems;

    debuglog("'s gems were altered by $gems by item {$item['id']}.");

    $out = [];

    if ($gems > 0)
    {
        $out[] = ['item.effect.gems.gain',
            [ 'gems' => $gems, 'itemName' => $item['name']],
            'module-inventory'
        ];
    }
    else
    {
        $out[] = ['item.effect.gems.lost',
            [ 'gems' => abs($gems), 'itemName' => $item['name']],
            'module-inventory'
        ];
    }

    return $out;
}
