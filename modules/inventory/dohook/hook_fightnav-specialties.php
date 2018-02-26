<?php
	require_once 'lib/itemhandler.php';
//	$constant = HOOK_FIGHTNAV;
//	$item = DB::prefix("item");
//	$inventory = DB::prefix("inventory");
//	$sql = "SELECT $item.* FROM $item WHERE ($item.activationhook & $constant) != 0";
//	$result = DB::query_cached($sql, "item-activation-$hookname");
	$args = display_item_fightnav($args);
