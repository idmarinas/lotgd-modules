<?php
// translator ready
// addnews ready
// mail ready

function inventory_getmoduleinfo()
{
	return [
		'name' => 'Inventory Basic System',
		'version' => '2.1.0',
		'author' => 'Christian Rutsch, Improved by <a href="//draconia.infommo.es">IDMarinas</a>',
		'category' => 'Inventory',
		'download' => 'http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1033',
//		'override_forced_nav' => true,
		'settings' => [
			'Inventory - Selling items, title',
				'sellgold' => 'how much gold does selling an item return? (percent), int|66',
				'sellgems' => 'how much gems does selling an item return? (percent), int|66',
			'Inventory - Carrying items, title',
				'limit' => 'How many items canbe carried by user?, int|0,1|0',
				'Note: Setting this to 0 will allow the user to carry a limitless amount of items, note',
				'weight' => 'Maximum weiht users can carry?, int|0,1|0',
				'Note: Setting this to 0 will allow the user to carry a limitless weight of items, note',
				'droppable' => 'Items are droppable?, bool|1',
            'Inventory - Setup,title',
                'enum_equip' => 'List of equipment as enum,text|,No where,head,On the Head,neck,Around the Neck,armor,As Armor,mainhand,As Weapon,belt,As Belt,offhand,As Shield,righthand,Right Gloves,trausers,On Pants,lefthand,Left Gloves,rightring,As right ring,feet,As Shoes,leftring,As left ring',
				'withcharstats' => 'Enable the charstat popup for the inventory,bool|0',
				'Please understand that this function is still in an early beta phase and not fully working!,note',
        ]
    ];
}
function inventory_install()
{
    require_once 'modules/inventory/install.php';

	return true;
}

function inventory_uninstall()
{
    require_once 'modules/inventory/uninstall.php';

	return true;
}

function inventory_dohook($hookname, $args)
{
    require "modules/inventory/dohook/hook_$hookname.php";

	return $args;
}

function inventory_run()
{
    require_once 'lib/itemhandler.php';

	mydefine('HOOK_NEWDAY', 1);
	mydefine('HOOK_FOREST', 2);
	mydefine('HOOK_VILLAGE', 4);
	mydefine('HOOK_SHADES', 8);
	mydefine('HOOK_FIGHTNAV', 16);
	mydefine('HOOK_TRAIN', 32);
	mydefine('HOOK_INVENTORY', 64);

    $op = httpget('op');

	require_once "modules/inventory/run/case_$op.php";
}

function inventory_showformitem($keyout, $val, $info)
{
    require_once 'lib/sanitize.php';

	rawoutput("<select name='$keyout'>");
	$sql = "SELECT itemid, name, class FROM ".DB::prefix("item")." ORDER BY class ASC";
	$result = DB::query($sql);
	$class = '';
    while ($row = DB::fetch_assoc($result))
    {
        if ($class != $row['class'])
        {
			rawoutput("<option value=''>=== {$row['class']} ===</option>");
			$class = $row['class'];
		}
		$name = full_sanitize($row['name']);
        if ($val == $row['itemid'])
        {
			$selected = 'selected';
        }
        else
        {
			$selected = '';
		}
		rawoutput("<option value='{$row['itemid']}' $selected>{$row['itemid']} - $name</option>");
	}
	rawoutput("</select>");
}
