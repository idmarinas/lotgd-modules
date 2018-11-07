<?php
// translator ready
// addnews ready
// mail ready

function inventorypopup_getmoduleinfo()
{
	return [
		'name' => 'Inventory Popup System',
		'version' => '1.0',
		'author' => 'Christian Rutsch',
		'category' => 'Inventory',
		'download' => 'http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1033',
		'override_forced_nav' => true,
    ];
}
function inventorypopup_install()
{
    module_addhook('charstats');

	return true;
}

function inventorypopup_uninstall() { return true; }

function inventorypopup_dohook($hookname, $args)
{
    global $session;

    if ($hookname == 'charstats')
    {
		$open = translate_inline("Open Inventory");
		addnav("runmodule.php?module=inventorypopup");
		addcharstat("Equipment Info");
		addcharstat("Inventory", "<a href='runmodule.php?module=inventorypopup' target='inventory' onClick=\"Lotgd.embed(this)\">$open</a>");
    }

	return $args;
}

function inventorypopup_run()
{
	global $session;

	require_once 'lib/itemhandler.php';
	require_once 'lib/sanitize.php';

	popup_header('Your Inventory');

	mydefine('HOOK_NEWDAY', 1);
	mydefine('HOOK_FOREST', 2);
	mydefine('HOOK_VILLAGE', 4);
	mydefine('HOOK_SHADES', 8);
	mydefine('HOOK_FIGHTNAV', 16);
	mydefine('HOOK_TRAIN', 32);
	mydefine('HOOK_INVENTORY', 64);

	$item = DB::prefix('item');
	$inventory = DB::prefix("inventory");
	$op2 = httpget('op2');
    $id = (int) httpget('id');

    switch($op2)
    {
        case 'equip':
            if ($session['user']['weaponvalue'] || $session['user']['armorvalue'])
            {
                output('`4Before you can equip this new item, you have to sell that old equipment.`0`n');

                break;
            }

			$thing = get_item($id);
			$sql = "SELECT $inventory.itemid FROM $inventory INNER JOIN $item ON $inventory.itemid = $item.itemid WHERE $item.equipwhere = '".$thing['equipwhere']."' AND $inventory.equipped = 1";
            $result = DB::query($sql);
            $wh = [];
			while ($row = DB::fetch_assoc($result)) $wh[] = $row['itemid'];
            if (is_array($wh) && count($wh))
            {
                modulehook('unequip-item', ['ids' => $wh]);

				$sql = "UPDATE $inventory SET equipped = 0 WHERE itemid IN (".join(",",$wh).")";
				DB::query($sql);
			}

			$sql = "UPDATE $inventory SET equipped = 1 WHERE itemid = $id AND userid = {$session['user']['acctid']} LIMIT 1";
            $result = DB::query($sql);

            modulehook('equip-item', ['id' => $id]);
		break;
		case 'unequip':
			modulehook('unequip-item', ['ids' => [$id]]);
			$sql = "UPDATE $inventory SET equipped = 0 WHERE itemid = $id AND userid = {$session['user']['acctid']}";
			$result = DB::query($sql);
		break;
		case 'drop':
			$id = httpget('id');
			$invid = httpget('invid');
			remove_item((int)$id, 1, false, $invid);
		break;
		case 'dropall':
			$id = httpget('id');
			$qty = httpget('qty');
			remove_item((int)$id, $qty);
		break;
		case 'activate':
            require_once 'lib/buffs.php';

			$id = httpget('id');
            $acitem = get_inventory_item((int)$id);

			if ($acitem['buffid'] > 0) apply_buff($acitem['name'], get_buff($acitem['buffid']));
			if ($acitem['charges'] > 1) uncharge_item((int)$id, 1);
            else remove_item((int)$id);

            if ($acitem['execvalue'] > '')
            {
                if ($acitem['exectext'] > '') { output($acitem['exectext'], $acitem['name']); }
                else { output("You activate %s!", $acitem['name']); }

				require_once 'lib/itemeffects.php';
				output_notl("`n`n%s`n", get_effect($acitem));
			}
		break;
	}

	modulehook('inventorypopup-stats');

	output("Your are currently wearing the following items:`n`n");
	$sql = "SELECT $item.*,
					MAX($inventory.equipped) AS equipped,
					COUNT($inventory.equipped) AS quantity,
					$inventory.sellvaluegold AS sellvaluegold,
					$inventory.sellvaluegems AS sellvaluegems,
					$inventory.invid AS invid
				FROM $item
				INNER JOIN $inventory ON $inventory.itemid = $item.itemid
				WHERE  $inventory.userid = {$session['user']['acctid']}
				GROUP BY $inventory.itemid
				ORDER BY $item.class ASC, $item.name ASC";
	/*$item.equippable = 0 AND*/
	$result = DB::query($sql);
	$inventory = [];
	foreach($result as $row)
	{
		$inventory[$row['class']][] = $row;
	}
    $inventory = modulehook('inventorypopup-inventory', ['inventory' => $inventory]);

    require_once 'lib/showtabs.php';

    lotgd_showtabs($inventory['inventory'], 'inventory_lotgd_showform', true);

	popup_footer();
}

function inventory_lotgd_showform($layout)
{
	global $session;

	if (is_array($layout) && empty($layout))
	{
		$html = "<table class='ui basic very compact unstackable striped table inventory'>";
		$html .= "<tr><td>".translate_inline("The inventory is empty")."</td></tr>";
		$html .= "</table>";

		return $html;
	}

	$wheres = ["righthand"=>"Right Hand","lefthand"=>"Left Hand","head"=>"Your Head","body"=>"Upper Body","arms"=>"Your Arms","legs"=>"Lower Body","feet"=>"Your Feet","ring1"=>"First Ring","ring2"=>"Second Ring","ring3"=>"Third Ring","neck"=>"Around your Neck","belt"=>"Around your Waist"];

	$html = "<table class='ui basic very compact unstackable striped table inventory'>";
	$i = 0;
	ksort($layout);
	foreach ($layout as $key => $val)
	{
		$html .= "<tr><td>";
		$html .= showRowItem($val);
		$html .= '</td></tr>';
	}
	$html .= "</table>";

	return appoencode($html, true);
}

//** Mostrar un item
function showRowItem($itsval)
{
	$equip = translate_inline("Equip");
	$unequip = translate_inline("Unequip");
	$activate = translate_inline("Activate");
	$drop = translate_inline("Drop 1");
	$dropall = translate_inline("Drop All");

	$html = "<table class='ui very basic very compact unstackable table items-list'><tr><td rowspan='2' class='center aligned collapsing'>";
	$html .= ($itsval['image']?'<i class="'.$itsval['image'].'"></i>':'');
	$html .= "<p>";
	if ($itsval['equipped'] && $itsval['equippable'])
	{
		$html .= "<a data-tooltip='$unequip' href='runmodule.php?module=inventorypopup&op2=unequip&id={$itsval['itemid']}'>`@<i class='toggle on icon'></i>`0</a> ";
		addnav("", "runmodule.php?module=inventorypopup&op2=unequip&id={$itsval['itemid']}");
	}
	else if ($itsval['equippable'] == 1)
	{
		$html .= "<a data-tooltip='$equip' href='runmodule.php?module=inventorypopup&op2=equip&id={$itsval['itemid']}'>`$<i class='toggle off icon'></i>`0</a> ";
		addnav("", "runmodule.php?module=inventorypopup&op2=equip&id={$itsval['itemid']}");
	}
	if ($itsval['activationhook'] & 64)
	{
		$html .= "<a data-tooltip='$activate' href='runmodule.php?module=inventorypopup&op2=activate&id={$itsval['itemid']}'>`!<i class='hand paper icon'></i>`0</a> ";
		addnav("", "runmodule.php?module=inventorypopup&op2=activate&id={$itsval['itemid']}");
	}
	if ($itsval['droppable'] == true)
	{
		$html .= "<a data-tooltip='$drop' href='runmodule.php?module=inventorypopup&op2=drop&id={$itsval['itemid']}&invid={$itsval['invid']}'>`Q<i class='recycle icon'></i>`0</a> ";
		$html .= "<a data-tooltip='$dropall' href='runmodule.php?module=inventorypopup&op2=dropall&id={$itsval['itemid']}&qty={$itsval['quantity']}'>`$<i class='bomb icon'></i>`0</a>";
		addnav("", "runmodule.php?module=inventorypopup&op2=drop&id={$itsval['itemid']}&invid={$itsval['invid']}");
		addnav("", "runmodule.php?module=inventorypopup&op2=dropall&id={$itsval['itemid']}&qty={$itsval['quantity']}");
	}
	$html .= '</p></td><td class="collapsing">';
	$html .= $itsval['equipped']?"<i class='asterisk icon'></i>":"";
	$html .= "({$itsval['quantity']}) `b{$itsval['name']}`b";
	$html .= "</td>";
	$html .= "<td nowrap>";
	$html .= sprintf("(Gold value: `^%s`0, Gem Value: `%%%s`0)", $itsval['sellvaluegold'], $itsval['sellvaluegems']);
	$tl_desc = translate_inline($itsval['description']);
	$html .= "</td></tr>";
	if ('' != $itsval['description'])
	{
		$html .= '<tr><td colspan="3">';
		$html .= "`i$tl_desc`i";
		$html .= '</td></tr>';
	}
	$html .= "</table>";

	return appoencode($html, true);
}
?>
