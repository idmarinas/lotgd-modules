<?php

/**
 * Increased/Decreased gems of player.
 *
 * @param int   $gems
 * @param array $item Data of item
 *
 * @return array|false Return false if nothing happend or an array of messages
 */
function itemeffects_increased_gems($gems, $item)
{
    //-- No gems to add/remove
    if (0 == $gems)
    {
        return false;
    }

    global $session;

    $session['user']['gems'] += $gems;

    debuglog("'s gems were altered by $gems by item {$item['itemid']}.");

    $out = [];

    if ($gems > 0)
    {
        if (1 == $gems)
        {
            $out[] = '`^You `@gain`^ one gem.`0`n';
        }
        else
        {
            $out[] = ['`^You `@gain`^ %s gems.`0`n', $gems];
        }
    }
    else
    {
        if (-1 == $gems)
        {
            $out[] = '`^You `$lose`^ one gem.`0`n';
        }
        else
        {
            $out[] = ['`^You `$lose`^ %s gems.`0`n', abs($gems)];
        }
    }

    return $out;
}
