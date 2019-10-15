<?php

/**
 * Alter charm of character.
 *
 * @param int   $points
 * @param array $item
 *
 * @return array An array of messages
 */
function itemeffects_alter_charm($points, $item): array
{
    global $session;

    $session['user']['charm'] += $points;

    if ($points > 0)
    {
        $out[] = [
            'item.effect.charm.gain',
            [ 'points' => $points, 'itemName' => $item['name']],
            'module-inventory'
        ];
    }
    else
    {
        $out[] = [
            'item.effect.charm.lost',
            [ 'points' => $points, 'itemName' => $item['name']],
            'module-inventory'
        ];
    }
}
