<?php

require_once 'lib/itemhandler.php';

$inventory = get_inventory();
$count = 0;
$ids = [];
while ($item = DB::fetch_assoc($inventory))
{
    if ($item['equipped'])
    {
        $loosechance = round($item['loosechance'] * .9, 0);
    }
    else { $loosechance = $item['loosechance']; }

	$destroyed = 0;
    for($c = 0; $c < $item['quantity']; $c++)
    {
		if($loosechance >= e_rand(1, 100)) $destroyed++;
    }

    if ($destroyed)
    {
        if ($item['equipped']) { $ids[] = $item['itemid']; }
        remove_item((int) $item['itemid'], $destroyed);
    }

	$count += $destroyed;
}

if (count($ids)) { modulehook('unequip-item', ['ids' => $ids]); }

if ($count == 1)
{
	output("`n`\$One of your items got damaged during the fight. ");
}
else if ($count > 1)
{
	output("`n`\$Overall `^%s`\$ of your items have been damaged during the fight.", $count);
}
