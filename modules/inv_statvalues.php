<?php
// translator ready
// addnews ready
// mail ready

function inv_statvalues_getmoduleinfo()
{
	return [
		'name' => 'Stat changing values for items',
		'version' => '1.0',
		'author' => 'Christian Rutsch',
		'category' => 'Inventory',
		'download' => 'http://www.dragonprime.net/users/XChrisX/itemsystem.zip',
		'override_forced_nav' => true,
		'requires' => [
			'inventory' => '2.0|By Christian Rutsch, part of the itemsystem',
        ],
		// 'settings' => [],
		'prefs-items' => [
			'Stat changes,title',
			'attack' => 'How much does this item increase the attack value?,int|0',
			'defense' => 'How much does this item increase the defense value?,int|0',
			'maxhitpoints' => 'How many hitpoints are granted by this item?,int|0',
        ]
    ];
}
function inv_statvalues_install()
{
	module_addhook('equip-item');
	module_addhook('unequip-item');
    module_addhook('dk-preserve');

	return true;
}

function inv_statvalues_uninstall() { return true; }

function inv_statvalues_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
		case 'equip-item':
			$id = $args['id'];
			debug($id);
			$attack = get_module_objpref('items', $id, 'attack');
			$defense = get_module_objpref('items', $id, 'defense');
			$maxhitpoints = get_module_objpref('items', $id, 'maxhitpoints');
			$session['user']['attack'] += $attack;
			$session['user']['defense'] += $defense;
			$session['user']['maxhitpoints'] += $maxhitpoints;
            if ($attack != 0 || $defense != 0 || $maxhitpoints != 0)
            {
				debuglog("'s stats changed due to equipping item $id: attack: $attack, defense: $defense, maxhitpoints: $maxhitpoints");
				debug("Your stats changed due to equipping item $id: attack: $attack, defense: $defense, maxhitpoints: $maxhitpoints");
			}
		break;
		case 'unequip-item':
            while(list($key, $id) = each($args['ids']))
            {
				$attack = - get_module_objpref('items', $id, 'attack');
				$defense = - get_module_objpref('items', $id, 'defense');
				$maxhitpoints = - get_module_objpref('items', $id, 'maxhitpoints');
				$session['user']['attack'] += $attack;
				$session['user']['defense'] += $defense;
                $session['user']['maxhitpoints'] += $maxhitpoints;

                if ($attack != 0 || $defense != 0 || $maxhitpoints != 0)
                {
					debuglog("'s stats changed due to unequipping item $id: attack: $attack, defense: $defense, maxhitpoints: $maxhitpoints");
					debug("Your stats changed due to unequipping item $id: attack: $attack, defense: $defense, maxhitpoints: $maxhitpoints");
				}
			}
		break;
		case 'dk-preserve':
			$sql = "SELECT itemid FROM inventory WHERE equipped = 1 AND userid = {$session['user']['acctid']}";
            $result = DB::query($sql);
            $unequip = [];
            while ($row = DB::fetch_assoc($result))
            {
                $id = $row['itemid'];
                $unequip[] = $id;
				$attack = - get_module_objpref('items', $id, 'attack');
				$defense = - get_module_objpref('items', $id, 'defense');
				$maxhitpoints = - get_module_objpref('items', $id, 'maxhitpoints');
				$session['user']['attack'] += $attack;
				$session['user']['defense'] += $defense;
				$session['user']['maxhitpoints'] += $maxhitpoints;
                if ($attack != 0 || $defense != 0 || $maxhitpoints != 0)
                {
                    debuglog("'s stats changed due to equipping item $id: attack: $attack, defense: $defense, maxhitpoints: $maxhitpoints");
					debug("Your stats changed due to unequipping item $id: attack: $attack, defense: $defense, maxhitpoints: $maxhitpoints");
                }
            }

            //-- Unequip all equipment
            if (count($unequip))
            {
                $updated = DB::updated('inventory');
                $updated->set(['equipped' => 0])
                    ->where->equalTo('userid', $session['user']['acctid'])
                        ->equalTo('equipped', 1)
                        ->in('itemid', $unequip)
                ;
                DB::execute($updated);
            }
        break;
    }

	return $args;
}

function inv_statvalues_run() {}
