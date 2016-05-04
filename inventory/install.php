<?php
	$item = db_prefix("item");
	$inventory = db_prefix("inventory");
	$itembuffs = db_prefix("itembuffs");

	// SQLs for creation of item-table

	$item_table = array(
		'itemid'=> array('name'=>'itemid', 'type'=>'int unsigned',	'extra'=>'auto_increment'),
		'class' => array('name'=>'class', 'type'=>'varchar(50)', 'null'=> '1',),
		'name' => array('name'=>'name', 'type'=>'varchar(50)', 'null'=>'0'),
		'description'  => array('name'=>'description', 'type'=>'text', 'null'=>'0'),
		'gold'=> array('name'=>'gold', 'type'=>'int unsigned', 'default'=>'0', 'null'=>'0'),
		'gems'=> array('name'=>'gems', 'type'=>'int unsigned', 'default'=>'0', 'null'=>'0'),
		'weight'=> array('name'=>'weight', 'type'=>'int unsigned', 'default'=>'0', 'null'=>'0'),
		'droppable'=> array('name'=>'droppable', 'type'=>'tinyint', 'default'=>'1', 'null'=>'0'),
		'level'=> array('name'=>'level', 'type'=>'tinyint unsigned', 'default'=>'1', 'null'=>'0'),
		'dragonkills'=> array('name'=>'dragonkills', 'type'=>'int unsigned', 'default'=>'0', 'null'=>'0'),
		'buffid'=> array('name'=>'buffid', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'charges'=> array('name'=>'charges', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'link'=> array('name'=>'link', 'type'=>'text', 'null'=>'0'),
		'hide'=> array('name'=>'hide', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'customvalue'=> array('name'=>'customvalue', 'type'=>'text', 'null'=>'0'),
		'execvalue'=> array('name'=>'execvalue', 'type'=>'text', 'null'=>'0'),
		'exectext'=> array('name'=>'exectext', 'type'=>'varchar(70)', 'null'=>'0'),
		'noeffecttext'=> array('name'=>'noeffecttext', 'type'=>'varchar(70)', 'null'=>'0'),
		'activationhook'=> array('name'=>'activationhook', 'type'=>'varchar(50)', 'default'=>'0', 'null'=>'0'),
		'findchance'=> array('name'=>'findchance', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'loosechance'=> array('name'=>'loosechance', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'dkloosechance'=> array('name'=>'dkloosechance', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'sellable'=> array('name'=>'sellable', 'type'=>'tinyint(2)', 'default'=>'1', 'null'=>'0'),
		'buyable'=> array('name'=>'buyable', 'type'=>'tinyint(2)', 'default'=>'1', 'null'=>'0'),
		'uniqueforserver'=> array('name'=>'uniqueforserver', 'type'=>'tinyint(2)', 'default'=>'0', 'null'=>'0'),
		'uniqueforplayer'=> array('name'=>'uniqueforplayer', 'type'=>'tinyint(2)', 'default'=>'0', 'null'=>'0'),
		//'equippable'=> array('name'=>'equippable', 'type'=>'tinyint(2)', 'default'=>'0', 'null'=>'0'),
		//'equipwhere'=> array('name'=>'equipwhere', 'type'=>'varchar(15)', 'default'=>'', 'null'=>'0'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'itemid'));

	$inventory_table = array(
		'invid'=>array('name'=>'invid', 'type'=>'int unsigned', 'null'=>'0', 'extra'=>'auto_increment'),
		'userid'=> array('name'=>'userid', 'type'=>'int unsigned'),
		'itemid' => array('name'=>'itemid', 'type'=>'int unsigned', 'null'=> '1',),
		'sellvaluegold' => array('name'=>'sellvaluegold', 'type'=>'int unsigned', 'null'=>'0'),
		'sellvaluegems' => array('name'=>'sellvaluegems', 'type'=>'int unsigned', 'null'=>'0'),
		'specialvalue' => array('name'=>'specialvalue', 'type'=>'text', 'null'=>'0'),
		'equipped' => array('name'=>'equipped', 'type'=>'tinyint(2)', 'null'=>'0'),
		'charges' => array('name'=>'charges', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'invid'));

	$buff_table = array(
		'buffid'=> array('name'=>'buffid', 'type'=>'int(10) unsigned',	'null'=>'0', 'extra'=>'auto_increment'),
		'buffname'=> array('name'=>'buffname', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'ccode'=> array('name'=>'ccode', 'type'=>'varchar(2)', 'null'=>1, 'default' => NULL),
		'buffshortname'=> array('name'=>'buffshortname', 'type'=>'varchar(50)', 'null'=>1, 'default' => NULL),
		'rounds'=> array('name'=>'rounds', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'invulnerable'=> array('name'=>'invulnerable', 'type'=>'tinyint(1) unsigned', 'null'=>0, 'default' => '0'),
		'dmgmod'=> array('name'=>'dmgmod', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'badguydmgmod'=> array('name'=>'badguydmgmod', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'atkmod'=> array('name'=>'atkmod', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'badguyatkmod'=> array('name'=>'badguyatkmod', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'defmod'=> array('name'=>'defmod', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'badguydefmod'=> array('name'=>'badguydefmod', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'lifetap'=> array('name'=>'lifetap', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'damageshield'=> array('name'=>'damageshield', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'regen'=> array('name'=>'regen', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'minioncount'=> array('name'=>'minioncount', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'maxbadguydamage'=> array('name'=>'maxbadguydamage', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'minbadguydamage'=> array('name'=>'minbadguydamage', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'maxgoodguydamage'=> array('name'=>'maxgoodguydamage', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'mingoodguydamage'=> array('name'=>'mingoodguydamage', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'startmsg'=> array('name'=>'startmsg', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'roundmsg'=> array('name'=>'roundmsg', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'wearoff'=> array('name'=>'wearoff', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'effectfailmsg'=> array('name'=>'effectfailmsg', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'effectnodmgmsg'=> array('name'=>'effectnodmgmsg', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'effectmsg'=> array('name'=>'effectmsg', 'type'=>'varchar(255)', 'null'=>1, 'default' => NULL),
		'allowinpvp'=> array('name'=>'allowinpvp', 'type'=>'tinyint(1) unsigned', 'null'=>0, 'default' => '0'),
		'allowintrain'=> array('name'=>'allowintrain', 'type'=>'tinyint(1) unsigned', 'null'=>0, 'default' => '0'),
		'survivenewday'=> array('name'=>'survivenewday', 'type'=>'tinyint(1) unsigned', 'null'=>0, 'default' => '0'),
		'expireafterfight'=> array('name'=>'expireafterfight', 'type'=>'tinyint(1) unsigned', 'null'=>0, 'default' => '0'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'buffid'));


	require_once("lib/tabledescriptor.php");
	synctable($item, $item_table, true);
	synctable($inventory, $inventory_table, true);
	synctable($itembuffs, $buff_table, true);

	module_addhook("superuser");
	module_addhook("dragonkill");
	module_addhook("battle-defeat");
	module_addhook("delete_character");

	module_addhook("fightnav-specialties");
	module_addhook("apply-specialties");
	module_addhook("newday");
	module_addhook("forest");
	module_addhook("village");
	module_addhook("shades");
	module_addhook("footer-train");

	module_addhook("showformextensions");
?>