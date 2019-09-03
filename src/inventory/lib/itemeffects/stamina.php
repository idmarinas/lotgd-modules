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
    if (! is_module_active('staminasystem') || 0 == $stamina)
    {
        return false;
    }

    require_once 'modules/staminasystem/lib/lib.php';

    $out = [];

    if ($stamina > 0)
    {
        $percent = get_stamina(3);

        addstamina($stamina);

        $staminaPercent = LotgdFormat::numeral(get_stamina(3) - $percent, 2);

        $out[] = ['`@Restore `b%s`b points of Stamina, about `b%s%%`b of your total Stamina by using `i%s`i.`0`n', LotgdFormat::numeral($stamina), $staminaPercent, $item['name']];

        debuglog("Restore $stamina points of Stamina by using {$item['itemid']}");
    }
    else
    {
        $percent = get_stamina(3);

        removestamina(abs($stamina));

        $staminaPercent = LotgdFormat::numeral($percent - get_stamina(3), 2);

        $out[] = ['`$Lost `b%s`b points of Stamina, about `b%s%%`b of your total Stamina by using `i%s`i.`0`n', LotgdFormat::numeral(abs($stamina)), $staminaPercent, $item['name']];

        debuglog("Lost $stamina points of Stamina by using {$item['itemid']}");
    }

    return $out;
}
