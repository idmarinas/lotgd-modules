<?php

/**
 * Alter deathpower of character.
 *
 * @param int   $points
 * @param array $item
 *
 * @return array An array of messages
 */
function itemeffects_alter_deathpower($points, $item): array
{
    global $session;

    $deathoverlord = getsetting('deathoverlord', '`$Ramius`0');
    $session['user']['deathpower'] += $points;

    if ($points > 0)
    {
        $out[] = [
            'item.effect.deathpower.gain',
            ['points' => $points, 'deathOverlord' => $deathoverlord, 'itemName' => $item['name']],
            'module-inventory',
        ];
    }
    else
    {
        $out[] = [
            'item.effect.deathpower.lost',
            ['points' => \abs($points), 'deathOverlord' => $deathoverlord, 'itemName' => $item['name']],
            'module-inventory',
        ];
    }

    return $out;
}
