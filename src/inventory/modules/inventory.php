<?php

\defined('HOOK_NEWDAY')    || \define('HOOK_NEWDAY', 1);
\defined('HOOK_FOREST')    || \define('HOOK_FOREST', 2);
\defined('HOOK_VILLAGE')   || \define('HOOK_VILLAGE', 4);
\defined('HOOK_SHADES')    || \define('HOOK_SHADES', 8);
\defined('HOOK_FIGHTNAV')  || \define('HOOK_FIGHTNAV', 16);
\defined('HOOK_TRAIN')     || \define('HOOK_TRAIN', 32);
\defined('HOOK_INVENTORY') || \define('HOOK_INVENTORY', 64);

function inventory_getmoduleinfo()
{
    return [
        'name'     => 'Item System',
        'version'  => '4.2.0',
        'author'   => 'Christian Rutsch, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Inventory',
        'download' => 'http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1033',
        'settings' => [
            'Inventory,title',
            'inventorylink' => 'Show inventory link for players?,bool|0',
            'Note: show inventory link in village,note',
            'data_imported' => 'Old data of inventory imported to new,viewonly',
            'Inventory - Selling items, title',
            'sellgold' => 'how much gold does selling an item return? (percent), int|66',
            'sellgems' => 'how much gems does selling an item return? (percent), int|66',
            'Inventory - Carrying items, title',
            'limit' => 'How many items canbe carried by user?, int|0',
            'Note: Setting this to 0 will allow the user to carry a limitless amount of items, note',
            'weight' => 'Maximum weiht users can carry?, int|0',
            'Note: Setting this to 0 will allow the user to carry a limitless weight of items, note',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function inventory_install()
{
    \Doctrine::createSchema([
        'LotgdLocal:ModInventory',
        'LotgdLocal:ModInventoryItem',
        'LotgdLocal:ModInventoryBuff',
    ], true);

    module_addhook('superuser');
    module_addhook('dragonkill');
    module_addhook('battle-defeat-end');
    module_addhook('character-cleanup');
    module_addhook('character-restore');

    module_addhook('fightnav-specialties');
    module_addhook('apply-specialties');
    module_addhook('newday');
    module_addhook('forest');
    module_addhook('village');
    module_addhook('shades');
    module_addhook('footer-train');
    module_addhook('bioend');

    return true;
}

function inventory_uninstall()
{
    \Doctrine::dropSchema([
        'LotgdLocal:ModInventory',
        'LotgdLocal:ModInventoryItem',
        'LotgdLocal:ModInventoryBuff',
    ]);

    return true;
}

function inventory_dohook($hookname, $args)
{
    global $session;

    require_once 'lib/itemhandler.php';

    $textDomain = 'module_inventory';

    if (\file_exists("modules/inventory/dohook/hook_{$hookname}.php"))
    {
        require "modules/inventory/dohook/hook_{$hookname}.php";
    }

    return $args;
}

function inventory_run()
{
    global $session;

    require_once 'lib/itemhandler.php';

    $op         = \LotgdRequest::getQuery('op');
    $textDomain = 'module_inventory';

    if (\file_exists("modules/inventory/run/case_{$op}.php"))
    {
        require_once "modules/inventory/run/case_{$op}.php";
    }
    else
    {
        require_once 'modules/inventory/run/case_.php';
    }
}
