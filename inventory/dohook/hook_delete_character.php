<?php
	$inventory = DB::prefix("inventory");
	$sql = "DELETE FROM $inventory WHERE userid = " . $args['acctid'];
	DB::query($sql);
?>