<?php
/**
 * Restore hitpoints.
 *
 * @param int   $hitpoins             Can be negative
 * @param array $item                 Data of item
 * @param bool  $overrideMaxhitpoints Allow restore more than maxhitpoints of character
 * @param bool  $canDie               Can die for effect of item?
 * @param mixed $hitpoints
 *
 * @return array An array of messages
 */
function itemeffects_restore_hitpoints($hitpoints, $item, $overrideMaxhitpoints = null, $canDie = true)
{
    global $session;

    $hitpoints            = (int) $hitpoints;
    $overrideMaxhitpoints = (bool) $overrideMaxhitpoints;
    $canDie               = (bool) $canDie;

    //-- Check max health to restore
    $maxRestoreHP = $session['user']['maxhitpoints'] - $session['user']['hitpoints'];

    //-- Not has health to restore
    if ($maxRestoreHP <= 0)
    {
        return [];
    }

    //-- It is not allowed to exceed the maximum health
    if ( ! $overrideMaxhitpoints)
    {
        $hitpoints = \min($hitpoints, $maxRestoreHP);
    }

    $out = [];

    if ($hitpoints > 0)
    {
        $session['user']['hitpoints'] += $hitpoints;

        $out[] = ['item.effect.health.gain',
            ['points' => $hitpoints, 'itemName' => $item['name']],
            'module_inventory',
        ];
        \LotgdLog::debug("Restored {$hitpoints} health points using the item {$item['id']}");
    }
    elseif ($hitpoints < 0)
    {
        $session['user']['hitpoints'] += $hitpoints;

        $out[] = ['item.effect.health.lost',
            ['points' => \abs($hitpoints), 'itemName' => $item['name']],
            'module_inventory',
        ];

        \LotgdLog::debug("Loss {$hitpoints} hitpoints using item {$item['id']}");
    }
    else
    {
        $out[] = ['item.effect.health.noeffect', ['itemName' => $item['name']], 'module_inventory'];
    }

    //-- Other messages
    if ($hitpoints && $hitpoints == $maxRestoreHP)
    {
        $out[] = ['item.effect.health.full',
            ['points' => \abs($hitpoints), 'itemName' => $item['name']],
            'module_inventory',
        ];
    }
    elseif ($session['user']['hitpoints'] <= 0 && ! $canDie)
    {
        $session['user']['hitpoints'] = 1;

        $out[] = ['item.effect.health.almost',
            ['points' => \abs($hitpoints), 'itemName' => $item['name']],
            'module_inventory',
        ];

        \LotgdLog::debug("Were almost killed when using item {$item['id']}");
    }
    elseif ($session['user']['hitpoints'] <= 0 && $canDie)
    {
        $session['user']['alive']     = false;
        $session['user']['hitpoints'] = 0;

        $out[] = ['item.effect.health.die',
            ['points' => \abs($hitpoints), 'itemName' => $item['name']],
            'module_inventory',
        ];

        \LotgdLog::debug("Died when used the item {$item['id']}");
    }

    return $out;
}
