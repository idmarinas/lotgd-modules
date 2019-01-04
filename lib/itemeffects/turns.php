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
    //-- Not do nothing if module Stamina is active
    if (is_module_active('staminasystem'))
    {
        return false;
    }

    //-- No turns restore
    if (0 == $turns)
    {
        return false;
    }

    global $session;

    $session['user']['turns'] += $turns;

    debuglog("'s turns were altered by $turns by item {$item['itemid']}.");

    $out = [];

    if ($turns > 0)
    {
        if (1 == $turns)
        {
            $out[] = '`^You `@gain`^ one turn.`0`n';
        }
        else
        {
            $out[] = ['`^You `@gain`^ %s turns.`0`n', $turns];
        }
    }
    else
    {
        if ($session['user']['turns'] <= 0)
        {
            $out[] = '`^You `$lose`^ all your turns.`0`n';
            $session['user']['turns'] = 0;
        }
        else
        {
            if (-1 == $turns)
            {
                $out[] = '`^You `$lose`^ one turn.`0`n';
            }
            else
            {
                $out[] = ['`^You `$lose`^ %s turns.`0`n', abs($turns)];
            }
        }
    }

    return $out;
}

/**
 * OBSOLETE.
 *
 * @param int   $hitpoins Can be negative
 * @param array $item     Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function restore_turns($turns, $item)
{
    trigger_error(sprintf(
        'Function %s is obsolete since 2.6.0; and delete in version 3.0.0 please use "%s" instead',
        __FUNCTION__,
        'itemeffects_restore_turns'
    ), E_USER_DEPRECATED);

    return itemeffects_restore_turns($turns, $item);
}
