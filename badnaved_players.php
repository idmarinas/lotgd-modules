<?php
/**
	01/10/09 - v0.0.2
	+ Made count number bold, red and blink when more than zero.
*/
function badnaved_players_getmoduleinfo()
{
	$info = array(
		"name"=>"Badnaved Players",
		"description"=>"Will check for and list players that are stuck in badnav land.",
		"version"=>"0.0.2",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?topic=10506.0",
	);
	return $info;
}

function badnaved_players_install()
{
	output("`c`b`Q%s 'badnaved_players' Module.`b`n`c", translate_inline(is_module_active('badnaved_players')?'Updating':'Installing'));
	module_addhook('superuser');
	return TRUE;
}

function badnaved_players_uninstall()
{
	output("`n`c`b`Q'badnaved_players' Module Uninstalled`0`b`c");
	return TRUE;
}

function badnaved_players_dohook($hookname,$args)
{
	global $session;

	myDefine('SU_FIX_BADNAVS',SU_MEGAUSER | SU_EDIT_PETITIONS | SU_EDIT_COMMENTS | SU_EDIT_USERS | SU_EDIT_CONFIG | SU_DEVELOPER );
	if( $session['user']['superuser'] & SU_FIX_BADNAVS )
	{
		$sql = "SELECT acctid
				FROM " . DB::prefix('accounts') . "
				WHERE restorepage LIKE '%badnav.php%'";
		$result = DB::query($sql);
		$count = DB::num_rows($result);
		$count = ( $count == 0 ) ? 0 : '`$`b'.$count.'`b`0';
		addnav('Actions');
		addnav(array('Badnaved Players (%s)',$count),'runmodule.php?module=badnaved_players',true);
	}

	return $args;
}

function badnaved_players_run()
{
	global $session;

	page_header('Badnaved Players');

	$op = httpget('op');
	if( $op == 'fix' )
	{
		$playerids = httppost('fixnav');
		if( is_array($playerids) && !empty($playerids) )
		{
			$i = 0;
			foreach( $playerids as $key => $acctid )
			{
				DB::query("UPDATE " . DB::prefix('accounts') . " SET allowednavs='', restorepage='', specialinc='' WHERE acctid='$acctid'");
				DB::query("DELETE FROM " . DB::prefix('accounts_output') . " WHERE acctid='$acctid'");
				$i++;
			}
			output('`n`@%s %s rescued from badnav land.`0`n', $i, translate_inline($i==1?'player was':'players were'));
		}
	}

	$sql = "SELECT acctid, name
			FROM " . DB::prefix('accounts') . "
			WHERE restorepage LIKE '%badnav%'";
	$result = DB::query($sql);

	if( DB::num_rows($result) > 0 )
	{
		output('`n`3To rescue players from badnav land, tick the box next to their name and click the button.`0`n');

		$name = translate_inline('Name');
		$fixnavs = translate_inline('Fix Navs');
		$submit = translate_inline('Fix Them');

		rawoutput('<form action="runmodule.php?module=badnaved_players&op=fix" method="POST">');
		addnav('','runmodule.php?module=badnaved_players&op=fix');
		rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999">');
		rawoutput('<tr class="trhead"><td>'.$name.'</td><td align="center">'.$fixnavs.'</td></tr>');
		$i = 0;
		while( $row = DB::fetch_assoc($result) )
		{
			rawoutput('<tr class="'.($i%2?'trlight':'trdark').'"><td>');
			output_notl('%s', $row['name']);
			rawoutput('</td><td align="center"><input type="checkbox" name="fixnav[]" value="'.$row['acctid'].'" /></tr>');
		}
		rawoutput('</table><br /><input type="submit" value="'.$submit.'" /></form>');
	}
	else
	{
		output('`n`3No players are trapped in badnav land at this time.`0');
	}

	addnav('Navigation');
	require_once('lib/superusernav.php');
	superusernav();

	page_footer();
}
?>
