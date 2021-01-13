<?php

// translator ready
// addnews ready
// mail ready

function inv_statvalues_getmoduleinfo()
{
    return [
        'name'                => 'Stat changing values for items',
        'version'             => '1.1.0',
        'author'              => 'Christian Rutsch, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category'            => 'Inventory',
        'download'            => 'http://www.dragonprime.net/users/XChrisX/itemsystem.zip',
        'override_forced_nav' => true,
        'requires'            => [
            'lotgd'     => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
            'inventory' => '>=3.0|By Christian Rutsch, part of the itemsystem',
        ],
        // 'settings' => [],
        'prefs-items' => [
            'Stat changes,title',
            'attack'       => 'How much does this item increase the attack value?,int|0',
            'defense'      => 'How much does this item increase the defense value?,int|0',
            'maxhitpoints' => 'How many hitpoints are granted by this item?,int|0',
        ],
    ];
}
function inv_statvalues_install()
{
    module_addhook('equip-item');
    module_addhook('unequip-item');
    module_addhook('dk-preserve');

    return true;
}

function inv_statvalues_uninstall()
{
    return true;
}

function inv_statvalues_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'equip-item':
            $id           = $args['itemid'];
            $attack       = get_module_objpref('items', $id, 'attack', 'inv_statvalues');
            $defense      = get_module_objpref('items', $id, 'defense', 'inv_statvalues');
            $maxhitpoints = get_module_objpref('items', $id, 'maxhitpoints', 'inv_statvalues');
            $session['user']['attack']       += $attack;
            $session['user']['defense']      += $defense;
            $session['user']['maxhitpoints'] += $maxhitpoints;

            $session['user']['weapondmg'] += $attack;
            $session['user']['armordef']  += $defense;

            if (0 != $attack || 0 != $defense || 0 != $maxhitpoints)
            {
                debuglog("'s stats changed due to equipping item {$id}: attack: {$attack}, defense: {$defense}, maxhitpoints: {$maxhitpoints}");
                \LotgdResponse::pageDebug("Your stats changed due to equipping item {$id}: attack: {$attack}, defense: {$defense}, maxhitpoints: {$maxhitpoints}");

                $args['inv_statvalues_result'] = true;
            }
        break;
        case 'unequip-item':
            if ( ! \is_array($args['ids']) || empty($args['ids']))
            {
                return $args;
            }

            foreach ($args['ids'] as $id)
            {
                $attack       = -get_module_objpref('items', $id, 'attack', 'inv_statvalues');
                $defense      = -get_module_objpref('items', $id, 'defense', 'inv_statvalues');
                $maxhitpoints = -get_module_objpref('items', $id, 'maxhitpoints', 'inv_statvalues');
                $session['user']['attack']       += $attack;
                $session['user']['defense']      += $defense;
                $session['user']['maxhitpoints'] += $maxhitpoints;

                $session['user']['weapondmg'] += $attack;
                $session['user']['armordef']  += $defense;

                if (0 != $attack || 0 != $defense || 0 != $maxhitpoints)
                {
                    $args['inv_statvalues_result'][$id] = true;
                    debuglog("'s stats changed due to unequipping item {$id}: attack: {$attack}, defense: {$defense}, maxhitpoints: {$maxhitpoints}");
                    \LotgdResponse::pageDebug("Your stats changed due to unequipping item {$id}: attack: {$attack}, defense: {$defense}, maxhitpoints: {$maxhitpoints}");
                }
            }
        break;
        case 'dk-preserve':
            $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
            $result     = $repository->findBy(['equipped' => 1, 'userId' => $session['user']['acctid']]);

            foreach ($result as $item)
            {
                $id = $item->getItem()->getId();

                $attack       = -get_module_objpref('items', $id, 'attack', 'inv_statvalues');
                $defense      = -get_module_objpref('items', $id, 'defense', 'inv_statvalues');
                $maxhitpoints = -get_module_objpref('items', $id, 'maxhitpoints', 'inv_statvalues');
                $session['user']['attack']       += $attack;
                $session['user']['defense']      += $defense;
                $session['user']['maxhitpoints'] += $maxhitpoints;

                $session['user']['weapondmg'] += $attack;
                $session['user']['armordef']  += $defense;

                if (0 != $attack || 0 != $defense || 0 != $maxhitpoints)
                {
                    debuglog("'s stats changed due to equipping item {$id}: attack: {$attack}, defense: {$defense}, maxhitpoints: {$maxhitpoints}");
                    \LotgdResponse::pageDebug("Your stats changed due to unequipping item {$id}: attack: {$attack}, defense: {$defense}, maxhitpoints: {$maxhitpoints}");
                }

                $item->setEquipped(false);

                \Doctrine::persist($item);
            }

            \Doctrine::flush();
        break;
        default: break;
    }

    return $args;
}

function inv_statvalues_run()
{
    // not used
}
