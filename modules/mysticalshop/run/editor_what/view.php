<?php
$mode = httpget( 'mode' );
if( $mode == 'delete' && is_numeric( $id ) )
{
	require_once( './modules/mysticalshop/run/editor_what/delete.php' );
	mysticalshop_delete_item( $id );
}

$edit = translate_inline("Edit");
$del = translate_inline("Delete");
$give = translate_inline("Preview");
$delconfirm = translate_inline("Are you sure you wish to delete this item?");
$sql = 'SELECT * FROM '.DB::prefix( 'magicitems' ).' WHERE id>=0 AND category='.$cat.' ORDER BY gold';
$result = DB::query( $sql );
$count = DB::num_rows($result);
if ($count == 0){
	if( $cat != 100 )
		output( '`6No items in %s department on record yet.`0', $names[$cat] );
	else
		output( '`6No items on record yet.`0' );
}else{
	$ops = translate_inline( 'Operations' );
	$itemid = translate_inline("Item ID");
	$name = translate_inline("Name");
	$goldc = translate_inline("Cost Gold");
	$gemc = translate_inline("Cost Gems");
	$cate = translate_inline("Category");
	rawoutput( '<table class="ui very compact striped table">' );
	rawoutput( '<thead><tr><th>'.$ops.'</th><th>'.$itemid.'</th><th>'.$name.'</th><th>'.$goldc.'</th><th>'.$gemc.'</th><th>'.$cate.'</th></tr></thead>' );
	$i = false;
	while($row = DB::fetch_assoc($result)){
		$id = $row['id'];
		$cat = $row['category'];
		$class = $i ? 'trlight' : 'trdark';
		rawoutput( '<tr class="'.$class.'"><td>[<a href="'.htmlentities( $fromeditor.'edit&id='.$id.'&cat=' ).$cat.'">'.$edit.'</a>|<a href="'.htmlentities( $fromeditor.'view&mode=delete&id='.$id.'&cat=' ).$cat.'" onClick="return confirm(\''.$delconfirm.'\');">'.$del.'</a>|<a href="'.htmlentities( $fromeditor.'preview&id='.$id.'&cat=' ).$cat.'">'.$give.'</a>]</td>' );
		addnav( '', $fromeditor.'edit&id='.$id.'&cat='.$cat );
		addnav( '', $fromeditor.'view&mode=delete&id='.$id.'&cat='.$cat );
		addnav( '', $fromeditor.'preview&id='.$id.'&cat='.$cat );
		rawoutput( '<td>'.$id.'</td><td>' );
		output_notl( '%s`0', $row['name'] );
		rawoutput( '</td><td>'.$row['gold'].'</td><td>'.$row['gems'].'</td><td>' );
		output_notl( '%s`0', $names[$cat] );
		rawoutput( '</td></tr>');
		rawoutput( '<tr class="'.$class.'"><td colspan="6">' );
		output_notl( '%s`0', $row['description'] );
		rawoutput("</td></tr>");
		$i = !$i;
	}
	rawoutput("</table>");
}
?>
