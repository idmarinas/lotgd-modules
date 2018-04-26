<?php
/**
 * Restore Stamina
 *
 * @var int $stamina Can be negative
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function restore_stamina($stamina)
{
    //-- Not do nothing if module Stamina is NOT active
    if (! is_module_active('staminasystem')) { return false; }

    //-- No stamina restore
    if ($stamina == 0) { return false; }

    global $item, $lotgdFormat;

    require_once 'modules/staminasystem/lib/lib.php';

    $out = [];

    if ($stamina > 0)
    {
        $percent = get_stamina(3);

        addstamina($stamina);

        $staminaPercent = $lotgdFormat->numeral(get_stamina(3) - $percent, 2);

        $out[] = ['`@Restore `b%s`b points of Stamina, about `b%s%%`b of your total Stamina by using `i%s`i.`0`n', $lotgdFormat->numeral($stamina), $staminaPercent, $item['name']];

        debuglog("Restore $stamina points of Stamina by using {$item['itemid']}");
    }
    else
    {
        $percent = get_stamina(3);

        removestamina(abs($stamina));

        $staminaPercent = $lotgdFormat->numeral($percent - get_stamina(3), 2);

        $out[] = ['`$Lost `b%s`b points of Stamina, about `b%s%%`b of your total Stamina by using `i%s`i.`0`n', $lotgdFormat->numeral(abs($stamina)), $staminaPercent, $item['name']];

        debuglog("Lost $stamina points of Stamina by using {$item['itemid']}");
    }

    return $out;
}
