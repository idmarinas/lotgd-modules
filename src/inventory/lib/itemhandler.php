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
    $info = datacache("item-full-info-{$item}", 86400, true);

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

            updatedatacache("item-full-info-{$item}", $info, true);
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
    $inventory = DB::prefix('inventory');
    $item = DB::prefix('item');

    // Max qty that user can have
    $qty = inventory_get_max_add($itemid, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems, $charges);

    if (0 != $maxcount && $totalcount >= $maxcount)
    {
        debug('Too many items, will not add this one!');

        return false;
    }
    elseif ($maxweight && $totalweight >= $maxweight)
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
        if (false === $sellvaluegold)
        {
            $sellvaluegold = round($item_raw['gold'] * (get_module_setting('sellgold', 'inventory') / 100));
        }

        if (false === $sellvaluegems)
        {
            $sellvaluegems = round($item_raw['gems'] * (get_module_setting('sellgems', 'inventory') / 100));
        }

        if (false === $charges)
        {
            $charges = $item_raw['charges'];
        }

        if ($item_raw['uniqueforserver'])
        {
            $sql = "SELECT * FROM $inventory WHERE itemid = $itemid LIMIT 2";
            $result = DB::query($sql);

            if (DB::num_rows($result) > 0)
            {
                debug('UNIQUE item has not been added because already someone else owns this!');

                return false;
            }
        }

        if ($item_raw['uniqueforplayer'])
        {
            $sql = "SELECT * FROM $inventory WHERE itemid = $itemid AND userid = $user LIMIT 2";
            $result = DB::query($sql);

            if (DB::num_rows($result) > 0)
            {
                debug('UNIQUEFORPLAYER item has not been added because this player already owns this item!');

                return false;
            }
        }
        $totalcount += $qty;
        $totalweight += $qty * $item_raw['weight'];
        $sql = "INSERT INTO $inventory (`userid`, `itemid`, `sellvaluegold`, `sellvaluegems`, `specialvalue`, `charges`) VALUES ";

        for ($i = 0; $i < $qty; $i++)
        {
            if ($i)
            {
                $sql .= ',';
            }
            $sql .= "($user, $itemid, $sellvaluegold, $sellvaluegems, '$specialvalue', '$charges')";
        }
        DB::query($sql);
        debuglog("has gained $qty item (ID: $itemid).", false, false, 'inventory');
        invalidatedatacache("inventory/user-$user");

        return $qty;
    }
}

function inventory_get_max_add($itemid, $qty = 1, $user = 0, $specialvalue = '', $sellvaluegold = false, $sellvaluegems = false, $charges = false)
{
    global $session, $totalcount, $totalweight, $item_raw, $maxweight, $maxcount;

    if (1 > $qty)
    {
        return false;
    }

    if (0 === $user)
    {
        $user = $session['user']['acctid'];
    }

    $item = DB::prefix('item');
    inventory_get_info($user);

    // We must not add more items than the player actually may carry!
    $sql = "SELECT name, gold, gems, charges, uniqueforserver, weight, uniqueforplayer FROM $item WHERE itemid = $itemid";
    $result = DB::query($sql);
    $item_raw = DB::fetch_assoc($result);
    $maxitems_count = max(0, $maxcount - $totalcount);

    if ($item_raw['weight'] > 0)
    {
        $maxitems_weight = max(0, floor(($maxweight - $totalweight) / $item_raw['weight']));
    }
    else
    {
        $maxitems_weight = $qty;
    }

    debug("Trying to add $qty items. Item's weight is {$item_raw['weight']}");

    if ($maxcount > 0)
    {
        debug("In theory only $maxitems_count should be added (totalcount)");
    }
    else
    {
        debug('There is no restriction on quantity active.');
    }

    if ($maxweight > 0)
    {
        debug("In theory only $maxitems_weight should be added (totalweight)");
    }
    else
    {
        debug('There is no restriction on weight active.');
    }

    if ($maxcount > 0 && $maxweight > 0 && $item_raw['weight'])
    {
        // limitation on total qty AND weight AND item is not weightless
        $qty = min($qty, $maxitems_count, $maxitems_weight);
        debug("Reducing real quantity to $qty. (count/weight-restriction)");
    }

    if ($maxweight > 0 && 0 == $maxcount && $item_raw['weight'] > 0)
    {
        // no limitation on total qty AND item is not weightless
        $qty = min($qty, $maxitems_weight);
        debug("Reducing real quantity to $qty. (weight-restriction)");
    }

    if ($maxcount > 0 && 0 == $maxweight)
    {
        // no limitation on weight.
        $qty = min($qty, $maxitems_count);
        debug("Reducing real quantity to $qty. (count-restriction)");
    }
    debug("Totalcount / MaxCount is: $totalcount / $maxcount");
    debug("MaxWeight is: $totalweight / $maxweight");
    debug('Item weight: '.$item_raw['weight']);
    debug("Quantity to add was: $qty");

    return $qty;
}

function inventory_get_info($user = 0): array
{
    global $session, $totalcount, $totalweight, $maxweight, $maxcount;

    $user = $user ?: $session['user']['acctid'];

    $query = \Doctrine::createQueryBuilder();
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
        $result = DB::query("SELECT itemid FROM item WHERE class IN('".join("','", $class)."')");
    }
    else
    {
        $result = DB::query("SELECT itemid FROM item WHERE class='$class'");
    }
    $ids = [];

    while ($id = DB::fetch_assoc($result))
    {
        $ids[] = $id;
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

    if (false === $user)
    {
        $user = $session['user']['acctid'];
    }

    if (false !== $invid)
    {
        $invsql = "AND invid = $invid";
    }
    else
    {
        $invsql = '';
    }
    $inventory = DB::prefix('inventory');
    $sql = "UPDATE $inventory SET charges = charges - 1 WHERE itemid = $itemid AND userid = $user AND charges >= 1 $invsql LIMIT 1";
    $result = DB::query($sql);

    if (0 == db_affected_rows($result))
    {
        debug('ERROR: Tried to uncharge item although no charges or no item present!');
    }
    else
    {
        debuglog('uncharged '.db_affected_rows($result)." items (ID: $itemid)", $user);
    }
    $sql = "DELETE FROM $inventory WHERE itemid = $itemid AND userid = $user AND charges = 0";
    $result = DB::query($sql);
    $count = db_affected_rows($result);

    if ($count)
    {
        debuglog("uncharged and deleted $count items (ID: $itemid)", $user);
    }
    invalidatedatacache("inventory/user-$user");
}

function recharge_item($itemid, $user = false, $invid = false)
{
    global $session;

    if (! is_int($itemid))
    {
        $itemid = get_item_id_from_name($itemid);
    }
    $itemid = (int) $itemid;

    if (false === $user)
    {
        $user = $session['user']['acctid'];
    }

    if (false !== $invid)
    {
        $invsql = "AND invid = $invid";
    }
    else
    {
        $invsql = '';
    }
    $inventory = DB::prefix('inventory');
    $sql = "UPDATE $inventory SET charges = charges 1 1 WHERE itemid = $itemid AND userid = $user $invsql LIMIT 1";
    $result = DB::query($sql);

    if (0 == db_affected_rows($result))
    {
        debug('ERROR: Tried to recharge non-present item!');
    }
    else
    {
        debuglog('recharged '.db_affected_rows($result)." items (ID: $itemid)", $user);
    }
    invalidatedatacache("inventory/user-$user");
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

    debuglog("removed item {$result[0]->getItem()->getName()} from inventory, qty $qty and real delete $affected", $user);

    invalidatedatacache("inventory/user-$user");
    invalidatedatacache("inventory/item-$item-$user");

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
