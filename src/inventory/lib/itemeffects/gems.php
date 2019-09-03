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
    global $session;

    $session['user']['gems'] += $gems;

    debuglog("'s gems were altered by $gems by item {$item['itemid']}.");

    $out = [];

    if ($gems > 0)
    {
        $out[] = ['`^You `@gain`0  %s %s.`0`n', $gems, \LotgdFormat::pluralize($gems, 'gem', 'gems')];

        return $out;
        if (1 == $gems)
        {
            $out[] = '`^You `@gain`^ one gem.`0`n';
        }
        else
        {
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

        return $out;
    }

    return false;
}
