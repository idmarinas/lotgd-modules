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
        return false;
    }

    global $session;

    $oldTurns = $session['user']['turns'];
    $session['user']['turns'] += $turns;

    debuglog("'s turns were altered by $turns by item {$item['itemid']}.");

    $out = [];

    if ($turns > 0)
    {
        $message = ['`^You `@gain`^ %s turns.`0`n', $turns];

        if (1 == $turns)
        {
            $message = '`^You `@gain`^ one turn.`0`n';
        }

        return [['`^You `@gain`0 %s %s.`0`n', $turns, \LotgdFormat::pluralize()]];
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
            $message = ['`^You `$lose`^ %s turns.`0`n', abs($turns)];

            if (-1 == $turns)
            {
                $message = '`^You `$lose`^ one turn.`0`n';
            }

            $out[]= $message;
        }
    }

    return $out;
}
