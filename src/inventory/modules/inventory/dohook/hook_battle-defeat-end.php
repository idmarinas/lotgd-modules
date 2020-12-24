<?php

//-- Not lost items in training
if ('train' == $args['options']['type'])
{
    return $args;
}

$repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
$inventory  = $repository->getInventoryOfCharacter($session['user']['acctid']);

$count = 0;
$ids   = [];

foreach ($inventory as $item)
{
    $loosechance = $item['item']['looseChance'];

    if ($item['equipped'])
    {
        $loosechance = \round($item['item']['looseChance'] * .9, 0);
    }

    $destroyed = 0;

    for ($c = 0; $c < $item['quantity']; ++$c)
    {
        if ($loosechance >= \mt_rand(1, 100))
        {
            ++$destroyed;
        }
    }

    if ($destroyed)
    {
        //-- Un-equip if are equipped
        if ($item['equipped'])
        {
            $ids[] = $item['item']['id'];
        }

        remove_item((int) $item['item']['id'], $destroyed);
    }

    $count += $destroyed;
}

if (\count($ids))
{
    modulehook('unequip-item', ['ids' => $ids]);
}

if ($count)
{
    $args['messages'][] = [
        'battle.defeated.end',
        ['n' => $count],
        $textDomain,
    ];
}
