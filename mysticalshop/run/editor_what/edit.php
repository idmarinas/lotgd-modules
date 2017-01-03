<?php
if( is_numeric( $id ) )
{
	$sql = 'SELECT * FROM '.DB::prefix( 'magicitems' ).' WHERE id='.$id.' LIMIT 1';
	$result = DB::query $sql );
	$row = array_merge( DB::fetch_assoc( $result ), $itemarray_extra_vals );

	rawoutput( '<form action="'.htmlentities( $fromeditor.'save&id='.$id.'&cat=' ).$cat.'" method="POST">' );
	addnav( '', $fromeditor.'save&id='.$id.'&cat='.$cat );
	require_once( 'lib/showform.php' );
	lotgd_showform( $itemarray, $row );
	rawoutput( '</form>' );
}
else
	output( 'Nothing to edit.' );
?>