<?php

// Itemhandler by Christian Rutsch (c) 2005

defined('HOOK_NEWDAY') || define('HOOK_NEWDAY', 1);
defined('HOOK_FOREST') || define('HOOK_FOREST', 2);
defined('HOOK_VILLAGE') || define('HOOK_VILLAGE', 4);
defined('HOOK_SHADES') || define('HOOK_SHADES', 8);
defined('HOOK_FIGHTNAV') || define('HOOK_FIGHTNAV', 16);
defined('HOOK_TRAIN') || define('HOOK_TRAIN', 32);
defined('HOOK_INVENTORY') || define('HOOK_INVENTORY', 64);

function display_item_fightnav($args)
{
    global $session;

    $script = $args['script'];

    $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
    $result = $repository->getItemsForNav(HOOK_FIGHTNAV, $session['user']['acctid']);

    if (! count($result))
    {
        return;
    }

    \LotgdNavigation::addHeader('navigation.category.items', ['textDomain' => 'module-inventory']);

    foreach ($result as $item)
    {
        if (! $item['item'] || 0 == $item['quantity'])
        {
            continue;
        }

        $link = "{$script}op=fight&skill=ITEM&l={$item['item']->getId()}&invid={$item['id']}";
        \LotgdNavigation::addNavNotl(sprintf('?%s `7(%s)`0', $item['item']->getName(), $item['quantity']), $link);
    }
}

function display_item_nav($hookname, $return = false)
{
    global $session;

    if ($hookname_override = \LotgdHttp::getQuery('hookname'))
    {
        $hookname = $hookname_override;
    }

    $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
    $result = $repository->getItemsForNav(constant('HOOK_'.strtoupper($hookname)), $session['user']['acctid']);

    if (! count($result))
    {
        return;
    }

    $returnPre = urlencode(\LotgdHttp::getServer('REQUEST_URI'));

    if ($return)
    {
        $returnPre = urlencode($return)."&returnhandle=1&hookname={$hookname}";
    }
    $return = \LotgdSanitize::cmdSanitize($returnPre);

    foreach ($result as $item)
    {
        if (! $item['item'] || 0 == $item['quantity'])
        {
            continue;
        }

        $link = "runmodule.php?module=inventory&op=activate&id={$item['item']->getId()}&invid={$item['id']}&return={$return}&hookname={$hookname}";

        \LotgdNavigation::addNav('navigation.nav.item.use', $link, [
            'textDomain' => 'module-inventory',
            'params' => [
                'name' => $item['item']->getName(),
                'quantity' => $item['quantity']
            ]
        ]);
    }
}

function run_newday_buffs($args): array
{
    global $session;

    $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
    $result = $repository->getItemsForNav(HOOK_NEWDAY, $session['user']['acctid']);

    if (! count($result))
    {
        return $args;
    }

    $messages = [];
    $alreadyDone = [];

    foreach ($result as $item)
    {
        $item['item'] = $repository->extractEntity($item['item']);

        if (isset($alreadyDone[$item['item']['id']]) && $alreadyDone[$item['item']['id']])
        {
            continue;
        }

        $alreadyDone[$item['item']['id']] = true; // prevent that more than one item of a kind activates...

        if ($item['buff'])
        {
            require_once 'lib/buffs.php';

            $item['buff'] = $repository->extractEntity($item['buff']);

            apply_buff($item['buff']['key'], array_merge([], ...array_map(
                function ($key, $value) { return [strtolower($key) => $value]; },
                array_keys($item['buff']),
                $item['buff']
            )));
        }

        if ($item['item']['execValue'] > '')
        {
            require_once 'lib/itemeffects.php';

            $message = ['item.activate', ['itemName' => $item['item']['name']], 'module-inventory'];

            if ($item['item']['execText'] > '')
            {
                $text = explode('|', $item['item']['execText']);
                $message = [$text[0], ['itemName' => $item['item']['name']], $text[1] ?? 'module-inventory'];
            }

            $messages[] = $message;
            $messages = array_merge($messages, get_effect($item, $item['noEffecttext']));
        }

        if ($item['charges'] > 1)
        {
            uncharge_item($item['item']['id'], false, $item['id']);
        }
        elseif (isset($item['id']))
        {//-- Inventory ID
            remove_item($item['item']['id'], 1, false, $item['id']);
        }
        else
        {
            remove_item($item['item']['id'], 1);
        }
    }

    $args['includeTemplatesPost']['inventory/dohook/newday.twig'] = [
        'messages' => $messages
    ];

    return $args;
}

function get_item_by_name($itemname)
{
    $repository = \Doctrine::getRepository('LotgdLocal:ModInventoryItem');
    $item = $repository->findOneBy([ 'name' => $itemname ]);

    if (! $item)
    {
        return false;
    }

    $item = $repository->extractEntity($item);
    $item['buff'] = $item['buff'] ? $repository->extractEntity($item['buff']) : null;

    return $item;
}

function get_item_by_id($itemid)
{
    $repository = \Doctrine::getRepository('LotgdLocal:ModInventoryItem');
    $item = $repository->find($itemid);

    if (! $item)
    {
        return false;
    }

    $item = $repository->extractEntity($item);
    $item['buff'] = $item['buff'] ? $repository->extractEntity($item['buff']) : null;

    return $item;
}

function get_item($item)
{
    if (! is_int($item))
    {
        $item = get_item_id_from_name($item);
    }

    return get_item_by_id($item);
}

/**
 * Get a full info of an item
 * Include Buff info
 * Include info of inv_statvalues if have.
 *
 * @param int $item
 *
 * @return array
 */
function get_item_full_info(int $item)
{
    $info = \LotgdCache::getItem("item-full-info-{$item}");

    if (! $info)
    {
        $repository = \Doctrine::getRepository('LotgdLocal:ModInventoryItem');
        $query = $repository->createQueryBuilder('u');
        $expr = $query->expr();

        $info = $query->select('u')
            ->addSelect('a.value AS attack')
            ->addSelect('d.value AS defense')
            ->addSelect('h.value AS maxhitpoints')

            ->leftJoin('LotgdCore:ModuleObjprefs', 'a', 'with', $expr->andX($expr->eq('a.setting', $expr->literal('attack')), $expr->eq('a.objtype', $expr->literal('items')), $expr->eq('a.objid', 'u.id')))

            ->leftJoin('LotgdCore:ModuleObjprefs', 'd', 'with', $expr->andX($expr->eq('d.setting', $expr->literal('defense')), $expr->eq('d.objtype', $expr->literal('items')), $expr->eq('d.objid', 'u.id')))

            ->leftJoin('LotgdCore:ModuleObjprefs', 'h', 'with', $expr->andX($expr->eq('h.setting', $expr->literal('maxhitpoints')), $expr->eq('h.objtype', $expr->literal('items')), $expr->eq('h.objid', 'u.id')))

            ->where('u.id = :id')

            ->setParameter('id', $item)

            ->setMaxResults(1)

            ->getQuery()
            ->getSingleResult()
        ;

        if ($info)
        {
            $info = array_merge($repository->extractEntity($info[0]), $info);
            unset($info[0]);
            $info['buff'] = $info['buff'] ? $repository->extractEntity($info['buff']) : null;

            \LotgdCache::setItem("item-full-info-{$item}", $info);
        }
    }

    return $info;
}

function add_item_by_name($itemname, $qty = 1, $user = 0, $specialvalue = '', $sellvaluegold = false, $sellvaluegems = false, $charges = false)
{
    $item = get_item_id_from_name($itemname);

    return add_item_by_id((int) $item, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems, $charges);
}

function add_item_by_id($itemid, $qty = 1, $user = 0, $specialvalue = '', $sellvaluegold = false, $sellvaluegems = false, $charges = false)
{
    global $session, $totalcount, $totalweight, $item_raw, $maxweight, $maxcount;

    if ($qty < 1)
    {
        return false;
    }

    $user = $user ?: $session['user']['acctid'];

    // Max qty that user can have
    $qty = inventory_get_max_add($itemid, $qty, $user);
    $inventoryStat = inventory_get_info($user);

    if (0 != $inventoryStat['inventoryLimitItems'] && $inventoryStat['inventoryCount'] >= $inventoryStat['inventoryLimitItems'])
    {
        debug('Too many items, will not add this one!');

        return false;
    }
    elseif ($inventoryStat['inventoryLimitWeight'] && $inventoryStat['inventoryWeight'] >= $inventoryStat['inventoryLimitWeight'])
    {
        debug("Items are too heavy. Item hasn't been added!");

        return false;
    }
    elseif ($qty <= 0)
    {
        debug('Zero items added.');

        return false;
    }
    else
    {
        $inventoryRepository = \Doctrine::getRepository('LotgdLocal:ModInventory');

        if (false === $sellvaluegold)
        {
            $sellvaluegold = round($item_raw->getGold() * (get_module_setting('sellgold', 'inventory') / 100));
        }

        if (false === $sellvaluegems)
        {
            $sellvaluegems = round($item_raw->getGems() * (get_module_setting('sellgems', 'inventory') / 100));
        }

        if (false === $charges)
        {
            $charges = $item_raw->getCharges();
        }

        if ($item_raw->getUniqueForServer())
        {
            $count = $inventoryRepository->count(['item' => $itemid]);

            if ($count)
            {
                debug('UNIQUE item has not been added because already someone else owns this!');

                return false;
            }
        }

        if ($item_raw->getUniqueForPlayer())
        {
            $count = $inventoryRepository->count(['item' => $itemid, 'userId' => $user]);

            if ($count)
            {
                debug('UNIQUEFORPLAYER item has not been added because this player already owns this item!');

                return false;
            }
        }

        $inventoryStat['inventoryCount'] += $qty;
        $inventoryStat['inventoryWeight'] += $qty * $item_raw->getWeight();

        for ($i = 0; $i < $qty; $i++)
        {
            $entity = $inventoryRepository->hydrateEntity([
                'userId' => $user,
                'item' => $item_raw,
                'sellValueGold' => $sellvaluegold,
                'sellValueGems' => $sellvaluegems,
                'specialValue' => $specialvalue,
                'charges' => $charges
            ]);
            \Doctrine::persist($entity);
        }

        \Doctrine::flush();

        debuglog("has gained $qty item (ID: $itemid).", false, false, 'inventory');
        \LotgdCache::removeItem("inventory/user-{$user}");

        return $qty;
    }
}

function inventory_get_max_add($itemid, $qty = 1, $user = 0)
{
    global $session, $item_raw;

    if (1 > $qty || ! $itemid)
    {
        return false;
    }

    $user = $user ?: $session['user']['acctid'];

    $inventoryStat = inventory_get_info($user);
    $repository = \Doctrine::getRepository('LotgdLocal:ModInventoryItem');

    // We must not add more items than the player actually may carry!
    $item_raw = $repository->find($itemid);
    $maxitems_count = max(0, $inventoryStat['inventoryLimitItems'] - $inventoryStat['inventoryCount']);

    $maxitems_weight = $qty;
    if ($item_raw->getWeight() > 0)
    {
        $maxitems_weight = max(0, floor(($inventoryStat['inventoryLimitWeight'] - $inventoryStat['inventoryWeight']) / $item_raw->getWeight()));
    }

    debug("Trying to add $qty items. Item's weight is {$item_raw->getWeight()}");

    if ($inventoryStat['inventoryLimitItems'] > 0)
    {
        debug("In theory only $maxitems_count should be added (totalcount)");
    }
    else
    {
        debug('There is no restriction on quantity active.');
    }

    if ($inventoryStat['inventoryLimitWeight'] > 0)
    {
        debug("In theory only $maxitems_weight should be added (totalweight)");
    }
    else
    {
        debug('There is no restriction on weight active.');
    }

    if ($inventoryStat['inventoryLimitItems'] > 0 && $inventoryStat['inventoryLimitWeight'] > 0 && $item_raw->getWeight())
    {
        // limitation on total qty AND weight AND item is not weightless
        $qty = min($qty, $maxitems_count, $maxitems_weight);
        debug("Reducing real quantity to $qty. (count/weight-restriction)");
    }

    if ($inventoryStat['inventoryLimitWeight'] > 0 && 0 == $inventoryStat['inventoryLimitItems'] && $item_raw->getWeight() > 0)
    {
        // no limitation on total qty AND item is not weightless
        $qty = min($qty, $maxitems_weight);
        debug("Reducing real quantity to $qty. (weight-restriction)");
    }

    if ($inventoryStat['inventoryLimitItems'] > 0 && 0 == $inventoryStat['inventoryLimitWeight'])
    {
        // no limitation on weight.
        $qty = min($qty, $maxitems_count);
        debug("Reducing real quantity to $qty. (count-restriction)");
    }

    debug("Totalcount / MaxCount is: {$inventoryStat['inventoryCount']} / {$inventoryStat['inventoryLimitItems']}");
    debug("MaxWeight is: {$inventoryStat['inventoryLimitWeight']} / {$inventoryStat['inventoryLimitWeight']}");
    debug('Item weight: '.$item_raw->getWeight());
    debug("Quantity to add was: $qty");

    return $qty;
}

function inventory_get_info($user = 0): array
{
    global $session, $totalcount, $totalweight, $maxweight, $maxcount;

    $user = $user ?: $session['user']['acctid'];

    $query = \Doctrine::createQueryBuilder();

    try
    {
        $result = $query->select('count(u.item) AS totalcount', 'sum(i.weight) AS totalweight')
            ->from('LotgdLocal:ModInventory', 'u')
            ->leftJoin('LotgdLocal:ModInventoryItem', 'i', 'with', $query->expr()->eq('i.id', 'u.item'))

            ->where('u.userId = :user')

            ->groupBy('u.userId')

            ->setParameter('user', $user)
            ->setMaxResults(1)

            ->getQuery()
            ->getSingleResult()
        ;
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);

        $result = [
            'totalcount' => 0,
            'totalweight' => 0
        ];
    }

    $totalcount = (int) $result['totalcount'];
    $totalweight = (int) $result['totalweight'];
    $maxcount = (int) get_module_setting('limit', 'inventory');
    $maxweight = (int) get_module_setting('weight', 'inventory');

    return [
        'inventoryCount' => $totalcount,
        'inventoryWeight' => $totalweight,
        'inventoryLimitItems' => $maxcount,
        'inventoryLimitWeight' => $maxweight
    ];
}

function add_item($item, $qty = 1, $user = 0, $specialvalue = '', $sellvaluegold = false, $sellvaluegems = false, $charges = false)
{
    if (! is_int($item))
    {
        return add_item_by_name($item, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems, $charges);
    }

    return add_item_by_id($item, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems, $charges);
}

function get_itemids_by_class($class)
{
    if (func_num_args() > 1 || is_array($class))
    {
        if (! is_array($class))
        {
            $class = func_get_args();
        }
    }

    $query = \Doctrine::createQueryBuilder();
    $query->select()
        ->from('LotgdLocal:ModInventoryItem', 'u')

        ->where('u.class = :class')

        ->setParameter('class', $class)
    ;

    if (is_array($class))
    {
        $query->where('u.class in (:class)');
    }

    $result = $query->getQuery()->getResult();

    $ids = [];

    foreach($result as $row)
    {
        $ids[] = $row->getId();
    }

    return $ids;
}

function uncharge_item($itemid, $user = false, $invid = false)
{
    global $session;

    if (! is_int($itemid))
    {
        $itemid = get_item_id_from_name($itemid);
    }
    $itemid = (int) $itemid;

    $user = $user ?: $session['user']['acctid'];

    $query = \Doctrine::createQueryBuilder();
    $query->where('u.item = :item AND u.userId = :user AND u.charges >= 1')

        ->setParameter('item', $itemid)
        ->setParameter('user', $user)

        ->setMaxResults(1)
    ;

    if ($invid)
    {
        $query->andWhere('u.id = :inv')
            ->setParameter('inv', $invid)
        ;
    }

    $entity = $query->getQuery()->getResult();

    if (count($entity))
    {
        $entity->setCharges($entity->getCharges() - 1);

        \Doctrine::persist($entity);

        \Doctrine::flush();

        debuglog('uncharged '.count($entity)." items (ID: $itemid)", $user);
    }
    else
    {
        debug('ERROR: Tried to uncharge item although no charges or no item present!');
    }

    $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
    $params = [
        'item' => $itemid,
        'userId' => $user,
        'charges' => 0
    ];

    $entities = $repository->findBy($params);

    foreach($entities as $entity)
    {
        \Doctrine::remove($entity);
    }

    \Doctrine::flush();

    if (count($entities))
    {
        $count = count($entities);
        debuglog("deleted $count items (ID: $itemid)", $user);
    }

    \LotgdCache::removeItem("inventory/user-{$user}");
}

function recharge_item($itemid, $user = false, $invid = false)
{
    global $session;

    if (! is_int($itemid))
    {
        $itemid = get_item_id_from_name($itemid);
    }
    $itemid = (int) $itemid;

    $user = $user ?: $session['user']['acctid'];

    $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
    $params = [
        'item' => $itemid,
        'userId' => $user
    ];

    if ($invid)
    {
        $params['id'] = $invid;
    }

    $entity = $repository->findOneBy($params);

    if ($entity)
    {
        $entity->setCharges($entity->getCharges() + 1);

        \Doctrine::persist($entity);

        \Doctrine::flush();
        debuglog("recharged 1 items (ID: $itemid)", $user);
    }
    else
    {
        debug('ERROR: Tried to recharge non-present item!');
    }

    \LotgdCache::removeItem("inventory/user-{$user}");
}

function check_qty_by_id($itemid, $user = 0)
{
    global $session;

    $user = $user ?: $session['user']['acctid'];

    $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');

    return $repository->count([ 'userId' => $user, 'item' => $itemid ]);
}

function get_item_id_from_name($itemname)
{
    $repository = \Doctrine::getRepository('LotgdLocal:ModInventoryItem');

    $query = $repository->createQueryBuilder('u');

    try
    {
        return $query->select('u.id')
            ->where('u.name = :name')

            ->setParameter('name', $itemname)

            ->getQuery()

            ->getSingleScalarResult()
        ;
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);

        return 0;
    }
}

function check_qty($item, $user = 0)
{
    if (! is_int($item))
    {
        $item = get_item_id_from_name($item);
    }

    return check_qty_by_id($item, $user);
}

function remove_item_by_id($item, $qty = 1, $user = false, $invid = false)
{
    global $session;

    $qty = (int) $qty;

    if (false === $user)
    {
        $user = $session['user']['acctid'];
    }

    $invid = (int) $invid;

    $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
    $query = $repository->createQueryBuilder('u');

    $query
        ->where('u.userId = :user AND u.item = :item')

        ->setParameter('user', $user)
        ->setParameter('item', $item)

        ->setMaxResults($qty)
    ;

    if ($invid)
    {
        $query->orWhere('u.id = :inv')
            ->setParameter('inv', $invid)
        ;
    }

    $result = $query->getQuery()->getResult();
    $affected = count($result);

    for ($i = 0; $i < $affected; $i++)
    {
        \Doctrine::remove($result[$i]);
    }

    \Doctrine::flush();

    if ($affected)
    {
        debuglog("removed item {$result[0]->getItem()->getName()} from inventory, qty $qty and real delete $affected", $user);
    }

    \LotgdCache::removeItem("inventory/user-{$user}");
    \LotgdCache::removeItem("inventory/item-{$item}-{$user}");

    return $affected;
}

function remove_item($item, $qty = 1, $user = false, $invid = false)
{
    if (! is_int($item))
    {
        $item = get_item_id_from_name($item);
    }

    return remove_item_by_id($item, $qty, $user, $invid);
}

function remove_items_by_class($class)
{
    $ids = get_itemids_by_class($class);

    foreach ($ids as $id)
    {
        remove_item((int) $id, check_qty((int) $id));
    }
}
