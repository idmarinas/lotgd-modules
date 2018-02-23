<?php
/*
 * Restore turns
 *
 * @var int $turns Can be negative
 *
 * return array|false Return false if nothing happend or an array of messages
 */
function restore_turns($turns)
{
    //-- Not do nothing if module Stamina is active
    if (is_module_active('staminasystem')) { return false; }

    //-- No turns restore
    if ($turns == 0) { return false; }

    global $session, $item;

    $session['user']['turns'] += $turns;

    debuglog("'s turns were altered by $turns by item {$item['itemid']}.");

    $out = [];
    if ($turns > 0)
    {
        if($turns == 1) { $out[] = '`^You `@gain`^ one turn.`n'; }
        else { $out[] = ['`^You `@gain`^ %s turns.`n', $turns]; }
    }
    else
    {
        if ($session['user']['turns'] <= 0)
        {
            $out[] = '`^You `$lose`^ all your turns.`n';
            $session['user']['turns'] = 0;
        }
        else
        {
            if($turns == -1) { $out[] = '`^You `$lose`^ one turn.`n'; }
            else { $out[] = ['`^You `$lose`^ %s turns.`n', abs($turns)]; }
        }
    }

    return $out;
}
