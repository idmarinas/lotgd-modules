<?php
	require_once("lib/itemhandler.php");
/*	$constant = constant("HOOK_" . strtoupper($hookname));
	$item = DB::prefix("item");
	$inventory = DB::prefix("inventory");
	$sql = "SELECT $item.* FROM $item WHERE ($item.activationhook & $constant) != 0";
	$result = DB::query_cached($sql, "item-activation-$hookname");*/
	display_item_nav("village", "village.php");
?>
