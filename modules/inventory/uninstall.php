<?php
	$item = DB::prefix("item");
	$inventory = DB::prefix("inventory");
	$itembuffs = DB::prefix("itembuffs");
	$sql = "DROP TABLE $item, $inventory, $itembuffs";
?>