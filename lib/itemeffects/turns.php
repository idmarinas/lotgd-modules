<?php
/**
 * Restore turns
 *
 * @param int $turns Can be negative
 * @param array $item Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function restore_turns($turns, $item)
{
    //-- Not do nothing if module Stamina is active
    if (is_module_active('staminasystem')) { return false; }

    //-- No turns restore
    if ($turns == 0) { return false; }

    global $session;

    $session['user']['turns'] += $turns;

    debuglog("'s turns were altered by $turns by item {$item['itemid']}.");

    $out = [];
    if ($turns > 0)
    {
        if($turns == 1) { $out[] = '`^You `@gain`^ one turn.`0`n'; }
        else { $out[] = ['`^You `@gain`^ %s turns.`0`n', $turns]; }
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
            if($turns == -1) { $out[] = '`^You `$lose`^ one turn.`0`n'; }
            else { $out[] = ['`^You `$lose`^ %s turns.`0`n', abs($turns)]; }
        }
    }

    return $out;
}
