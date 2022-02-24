<?php
/**
 * Restore turns.
 *
 * @param int   $turns Can be negative
 * @param array $item  Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_restore_turns($turns, $item)
{
    //-- Not do nothing if module Stamina is active or No turns restore
    if (is_module_active('staminasystem') || 0 == $turns)
    {
        return [];
    }

    global $session;

    $session['user']['turns'] += $turns;

    LotgdLog::debug("'s turns were altered by {$turns} by item {$item['id']}.");

    $out = [];

    if ($turns > 0)
    {
        $out[] = ['item.effect.turns.gain',
            ['turns' => $turns, 'itemName' => $item['name']],
            'module_inventory',
        ];
    }
    else
    {
        $out[] = ['item.effect.turns.lost',
            ['turns' => \abs($turns), 'itemName' => $item['name']],
            'module_inventory',
        ];

        if ($session['user']['turns'] <= 0)
        {
            $out[] = ['item.effect.turns.lost.all',
                ['turns' => \abs($turns), 'itemName' => $item['name']],
                'module_inventory',
            ];
            $session['user']['turns'] = 0;
        }
    }

    return $out;
}
