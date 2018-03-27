<?php

/**
 * Increased/Decreased gems of player
 *
 * @param int $gems
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_increased_gems($gems)
{
    //-- No gems to add/remove
    if ($gems == 0) { return false; }

    global $session, $item;

    $session['user']['gems'] += $gems;

    debuglog("'s gems were altered by $gems by item {$item['itemid']}.");

    $out = [];
    if ($gems > 0)
    {
        if($gems == 1) { $out[] = '`^You `@gain`^ one gem.`0`n'; }
        else { $out[] = ['`^You `@gain`^ %s gems.`0`n', $gems]; }
    }
    else
    {
        if($gems == -1) { $out[] = '`^You `$lose`^ one gem.`0`n'; }
        else { $out[] = ['`^You `$lose`^ %s gems.`0`n', abs($gems)]; }
    }

    return $out;
}
