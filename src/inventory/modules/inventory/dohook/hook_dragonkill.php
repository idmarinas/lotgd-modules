<?php

$repository = Doctrine::getRepository('LotgdLocal:ModInventory');
$inventory  = $repository->getInventoryOfCharacter($session['user']['acctid']);

$ids   = [];
$count = 0;

foreach ($inventory as $item)
{
    $destroyed = 0;

    for ($i = 0; $i < $item['quantity']; ++$i)
    {
        if ($item['item']['dkLooseChance'] >= \mt_rand(1, 100))
        {
            ++$destroyed;
        }
    }

    if ($destroyed !== 0)
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

if ( ! empty($ids))
{
    modulehook('unequip-item', ['ids' => $ids]);
}

if ($count !== 0)
{
    LotgdFlashMessages::addErrorMessage(LotgdTranslator::t('flash.message.dragonkill', ['n' => $count], $textDomain));
}
