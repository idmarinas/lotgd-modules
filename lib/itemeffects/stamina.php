<?php
/**
 * Restore Stamina
 *
 * @param int $stamina Can be negative
 * @param array $item Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_restore_stamina($stamina, $item)
{
    //-- Not do nothing if module Stamina is NOT active
    if (! is_module_active('staminasystem')) { return false; }

    //-- No stamina restore
    if ($stamina == 0) { return false; }

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

/**
 * OBSOLETE
 *
 * @param int $hitpoins Can be negative
 * @param array $item Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function restore_stamina($stamina, $item)
{
    trigger_error(sprintf(
        'Function %s is obsolete since 2.6.0; and delete in version 3.0.0 please use "%s" instead',
        __FUNCTION__,
        'itemeffects_restore_stamina'
    ), E_USER_DEPRECATED);

    return itemeffects_restore_stamina($stamina, $item);
}
