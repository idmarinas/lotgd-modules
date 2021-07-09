<?php

$id    = (int) \LotgdRequest::getQuery('id');
$invId = (int) \LotgdRequest::getQuery('invid');
$op2   = \LotgdRequest::getQuery('op2');

$repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
$accountRep = \Doctrine::getRepository('LotgdCore:Accounts');

\LotgdResponse::pageStart('title.inventory', ['name' => \LotgdSanitize::fullSanitize($session['user']['name'])], $textDomain);

\LotgdNavigation::addHeader('common.category.navigation', ['textDomain' => 'navigation_app']);
\LotgdNavigation::villageNav();
\LotgdNavigation::addNav('navigation.nav.update', 'runmodule.php?module=inventory', ['textDomain' => $textDomain]);

if ('dropitem' == $op2)
{
    remove_item($id, 1, $session['user']['acctid'], $invId);
}
elseif ('equip' == $op2 && ($session['user']['weaponvalue'] || $session['user']['armorvalue']))
{
    \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('item.equip.old', [], $textDomain));
}
elseif ('equip' == $op2)
{
    require_once 'lib/itemeffects.php';

    $entity = $repository->findOneBy(['id' => $invId, 'item' => $id]);

    $flashType    = 'addErrorMessage';
    $flashMessage = 'item.equip.error';
    $flashParams  = [];

    if ($entity)
    {
        $flashType    = 'addErrorMessage';
        $flashMessage = 'item.equip.requisites';
        $flashParams  = ['itemName' => $entity->getItem()->getName()];

        if (inventory_can_use_item($repository->extractEntity($entity->getItem())))
        {
            $flashType    = 'addSuccessMessage';
            $flashMessage = 'item.equip.success';

            $query = $repository->createQueryBuilder('u');
            $expr  = $query->expr();

            $result = $query->select('u')
                ->leftJoin('LotgdLocal:ModInventoryItem', 'i', 'with', $expr->eq('i.id', 'u.item'))
                ->where('u.equipped = 1 AND u.userId = :user AND i.equippable = 1 AND i.equipWhere = :where')

                ->setParameter('where', $entity->getItem()->getEquipWhere())
                ->setParameter('user', $session['user']['acctid'])

                ->getQuery()
                ->getResult()
            ;

            foreach ($result as $key => $value)
            {
                $unequip = modulehook('unequip-item', ['ids' => [$value->getItem()->getId()]]);

                if ($unequip['inv_statvalues_result'][$value->getItem()->getId()] ?? false)
                {
                    $value->setEquipped(false);

                    \Doctrine::persist($value);
                }
            }

            $result = modulehook('equip-item', ['itemid' => $id]);

            if ($result['inv_statvalues_result'] ?? false)
            {
                $flashType    = 'addSuccessMessage';
                $flashMessage = 'item.equip.success';

                $entity->setEquipped(true);

                \Doctrine::persist($entity);
            }

            \Doctrine::flush();
        }
    }

    \LotgdFlashMessages::{$flashType}(\LotgdTranslator::t($flashMessage, $flashParams, $textDomain));
}
elseif ('unequip' == $op2)
{
    $entity = $repository->findOneBy(['id' => $invId, 'item' => $id]);

    $flashType    = 'addErrorMessage';
    $flashMessage = 'item.unequip.error';
    $flashParams  = [];

    //-- If found item in inventory
    if ($entity)
    {
        $result = modulehook('unequip-item', ['ids' => [$id]]);

        //-- Success unequip
        if ($result['inv_statvalues_result'][$id] ?? false)
        {
            $entity->setEquipped(false);

            \Doctrine::persist($entity);
            \Doctrine::flush();

            $flashType    = 'addSuccessMessage';
            $flashMessage = 'item.unequip.success';
            $flashParams  = ['itemName' => $entity->getItem()->getName()];
        }
    }

    \LotgdFlashMessages::{$flashType}(\LotgdTranslator::t($flashMessage, $flashParams, $textDomain));
}
elseif ('activate' == $op2)
{
    $item = $repository->getItemOfInventoryOfCharacter($id, $session['user']['acctid']);

    if ($item['charges'] > 1)
    {
        uncharge_item($id, false, $invId);
    }
    elseif (isset($invId))
    {
        remove_item($id, 1, false, $invId);
    }
    else
    {
        remove_item($id, 1);
    }

    if (($item['item']['buff'] ?? false) && ! empty($item['item']['buff']))
    {
        LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff($item['item']['buff']['key'], \array_merge([], ...\array_map(
            function ($key, $value)
            {
                return [\strtolower($key) => $value];
            },
            \array_keys($item['item']['buff']),
            $item['item']['buff']
        )));
    }

    if ($item['item']['execvalue'] > '')
    {
        $messageText       = 'item.activate';
        $messageTextDomain = $textDomain;

        if ($item['item']['exectext'] > '')
        {
            $text              = \explode('|', $item['item']['exectext']);
            $messageText       = $text[0];
            $messageTextDomain = $text[1] ?? $textDomain;
        }

        \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t($messageText, ['itemName' => $item['item']['name']], $messageTextDomain));

        require_once 'lib/itemeffects.php';

        $result = get_effect($item['item'], $item['item']['noEffectText']);

        $messages = '';

        foreach ($result as $message)
        {
            $messages .= \LotgdTranslator::t($message[0], $message[1], $message[2]);
        }

        if ($messages)
        {
            \LotgdFlashMessages::addInfoMessage($messages);
        }
    }
}

$params = [
    'textDomain'  => $textDomain,
    'inventory'   => $repository->getInventoryOfCharacter($session['user']['acctid']),
    'limitTotal'  => get_module_setting('limit', 'inventory'),
    'weightTotal' => get_module_setting('weight', 'inventory'),
];

\LotgdResponse::pageAddContent(\LotgdTheme::render('@module/inventory/run/inventory.twig', $params));

\LotgdResponse::pageEnd();
