<?php
/*
 * Restore hitpoints
 *
 * @var int $hitpoins Can be negative
 * @var bool $overrideMaxhitpoints Allow restore more than maxhitpoints of character
 * @var bool $canDie Can die for effect of item?
 *
 * return array|false Return false if nothing happend or an array of messages
 */
function restore_hitpoints($hitpoints, $overrideMaxhitpoints = false, $canDie = true)
{
	global $session, $item;

    $hitpoints = (int) $hitpoints;
    $overrideMaxhitpoints = (bool) $overrideMaxhitpoints;
    $canDie = (bool) $canDie;

	//-- Check max health to restore
	$maxRestoreHP = $session['user']['maxhitpoints'] - $session['user']['hitpoints'];

	//-- Not has health to restore
	if ($maxRestoreHP <= 0) { return false; }

    //-- It is not allowed to exceed the maximum health
	if (! $overrideMaxhitpoints) { $hitpoints = min($hitpoints, $maxRestoreHP); }

    $out = [];
	if ($hitpoints > 0)
	{
		if ($hitpoints == $maxRestoreHP)
		{
			$session['user']['hitpoints'] += $hitpoints;
			$out[] = sprintf_translate('`^Your hitpoints have been `@fully`^ restored.`0`n');
		}
		else
		{
			$session['user']['hitpoints'] += $hitpoints;
			$out[] = sprintf_translate('`^You have been `@healed`^ for %s points.`0`n', $hitpoints);
        }

		debuglog("Restored $hitpoints health points using the item {$item['itemid']}");
	}
	elseif ($hitpoints < 0)
	{
        $session['user']['hitpoints'] += $hitpoints;

        if ($session['user']['hitpoints'] > 0)
        {
			$out[] = sprintf_translate('`^You `4loose`^ %s hitpoints.`0`n', abs($hitpoints));
			debuglog("Loss $hitpoints hitpoints using item {$item['itemid']}");
        }
        else if ($session['user']['hitpoints'] <= 0 && false == $canDie)
        {
			$session['user']['hitpoints'] = 1;
            $out[] = sprintf_translate('`^You were `$almost`^ killed.`0`n');
			debuglog("Were almost killed when using item {$item['itemid']}");
        }
        else
        {
			$session['user']['hitpoints'] = 0;
			$session['user']['alive'] = 0;
			$out[] = sprintf_translate('`$You die. ¡What a pity!.`0`n');
			debuglog("Died when I used the item {$item['itemid']}");
        }
	}
	else { $out[] = sprintf_translate('`&You used "`i%s`i" but it had no effect.`0`n',$item['name']); }

	return $out;

}
