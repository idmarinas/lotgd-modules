<?php
if( $session['user']['superuser'] & SU_EDIT_MOUNTS )
{
	addnav( 'Module Configurations' );

	$ed_cats = DB::fetch_assoc(
			DB::query( 'SELECT category FROM '.DB::prefix( 'magicitems' ).' GROUP BY category ORDER BY category')
		);
	$cat = $ed_cats['category'];
	if( $cat == trim( '' ) || $cat === false )
		$cat = 100;

	addnav( 'Equipment Editor', $from.'op=editor&what=view&cat='.$cat );
}
?>
