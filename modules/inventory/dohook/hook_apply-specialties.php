<?php
require_once 'lib/itemhandler.php';

$skill = httpget('skill');
if ($skill == 'ITEM')
{
    require_once 'lib/buffs.php';

	$itemid = (int)httpget('l');
	$invid = httpget('invid');
    $item = get_inventory_item_by_id($itemid, $invid);

    if ($item['buffid'] > 0)
    {
		apply_buff($item['name'], get_buff($item['buffid']));
	}
    if ($item['execvalue'] > '')
    {
        require_once 'lib/itemeffects.php';

        global $countround, $lotgdBattleContent;

        if ($item['exectext'] > '')
        {
			$lotgdBattleContent['battlerounds'][$countround]['allied'][] = sprintf(translate_inline($item['exectext']), $item['name']);
        }
        else
        {
			$lotgdBattleContent['battlerounds'][$countround]['allied'][] = sprintf(translate_inline('You activate %s!'), $item['name']);
        }

        $lotgdBattleContent['battlerounds'][$countround]['allied'][] = sprintf('%s`n', get_effect($item, $item['noeffecttext']));
    }

    if ($item['charges'] > 1)
    {
		uncharge_item((int)$itemid, false, $invid);
    }
    else if (isset($item['invid']))
    {
		remove_item((int)$itemid, 1, false, $invid);
    }
    else
    {
		remove_item((int)$itemid, 1);
	}
}
