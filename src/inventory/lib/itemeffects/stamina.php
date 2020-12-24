<?php
/**
 * Restore Stamina.
 *
 * @param int   $stamina Can be negative
 * @param array $item    Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_restore_stamina($stamina, $item)
{
    //-- Not do nothing if module Stamina is NOT active or No stamina to restore
    if ( ! is_module_active('staminasystem') || 0 == $stamina)
    {
        return false;
    }

    require_once 'modules/staminasystem/lib/lib.php';

    $out = [];

    if ($stamina > 0)
    {
        $percent = get_stamina(3);

        addstamina($stamina);

        $staminaPercent = get_stamina(3) - $percent;

        $out[] = ['item.effect.stamina.gain',
            ['points' => $stamina, 'percent' => $staminaPercent, 'itemName' => $item['name']],
            'module-inventory',
        ];

        debuglog("Restore {$stamina} points of Stamina by using {$item['id']}");
    }
    else
    {
        $percent = get_stamina(3);

        removestamina(\abs($stamina));

        $staminaPercent = $percent - get_stamina(3);

        $out[] = ['item.effect.stamina.lost',
            ['points' => \abs($stamina), 'percent' => $staminaPercent, 'itemName' => $item['name']],
            'module-inventory',
        ];

        debuglog("Lost {$stamina} points of Stamina by using {$item['id']}");
    }

    return $out;
}
