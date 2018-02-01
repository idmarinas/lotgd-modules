<?php
/*

*/

function petitionfixnavs_getmoduleinfo()
{
	return [
	    'name' => 'Fixnavs in petitions',
		'version' => '1.0.1',
		'author' => '`2Oliver Brendel',
		'category' => 'Administrative',
		'download' => 'http://dragonprime.net/dls/petitionfixnavs.zip',
    ];
}

function petitionfixnavs_install()
{
    module_addhook_priority("footer-viewpetition");

	return true;
}

function petitionfixnavs_uninstall()
{
	return true;
}


function petitionfixnavs_dohook($hookname, $args)
{
    global $session;

	switch ($hookname)
	{
        case "footer-viewpetition":
            if (httpget('op')!='view') return $args;
            addnav("Navigation");
            $id=httpget('id');
            addnav("Fix this users navs","runmodule.php?module=petitionfixnavs&id=$id");
            //query took from viewpetition.php
            $nextsql="SELECT p1.petitionid, p1.status FROM ".DB::prefix("petitions")." AS p1, ".DB::prefix("petitions")." AS p2 WHERE p1.petitionid>'$id' AND p2.petitionid='$id' AND p1.status=p2.status ORDER BY p1.petitionid ASC LIMIT 1";
            $nextresult=DB::query($nextsql);
            $nextrow=DB::fetch_assoc($nextresult);
            if ($nextrow){
                $nextid=$nextrow['petitionid'];
                $s=$nextrow['status'];
                // $status=$statuses[$s];
                addnav("Close and next","runmodule.php?module=petitionfixnavs&op=cnext&id=$id&nextid=$nextid");
                addnav("Fix,close and next","runmodule.php?module=petitionfixnavs&op=cfnext&id=$id&nextid=$nextid");
            }
            break;
    }

	return $args;
}

function petitionfixnavs_run()
{
    global $session;

    page_header("Fixed Navs");

	$id = httpget('id');
    $op = httpget('op');

    switch ($op)
    {
		case "cnext":
			$nextid=httpget('nextid');
			$sql="UPDATE ".DB::prefix("petitions")." SET status=2,closeuserid='{$session['user']['acctid']}',closedate='".date("Y-m-d H:i:s")."' WHERE petitionid='$id'";
			DB::query($sql);
			pt_insert(translate_inline("/me has closed the petition"));
			invalidatedatacache("petition_counts");
			redirect("viewpetition.php?op=view&id=$nextid");
			break;
		case "cfnext":
			$sql="SELECT author FROM ".DB::prefix("petitions")." WHERE petitionid='$id'";
			$result=DB::query($sql);
			$author=DB::fetch_assoc($result);
			debug($author);
            if (!$author)
            {
				output_notl("Error! Please give a detailed error report to the Petition Fixnavs Module Author!");
                page_footer();

				return;
			}
			$author=$author['author'];
			$sql="UPDATE ".DB::prefix("accounts")." SET allowednavs='',specialinc='' WHERE acctid=$author";
			$result=DB::query($sql);
			$sql="UPDATE ".DB::prefix("accounts_everypage")." SET allowednavs='' WHERE acctid=$author";
			$result=DB::query($sql);
			invalidatedatacache("accounts/account_".$author);
			$sql="DELETE FROM ".DB::prefix("accounts_output")." WHERE acctid=$author";
			$result=DB::query($sql);
			$nextid=httpget('nextid');
			$sql="UPDATE ".DB::prefix("petitions")." SET status=2,closeuserid='{$session['user']['acctid']}',closedate='".date("Y-m-d H:i:s")."' WHERE petitionid='$id'";
			DB::query($sql);
            pt_insert(translate_inline("/me has fixed navs and closed the petition"));

            require_once 'lib/systemmail.php';

			systemmail($author,array("Your petition"),array("Your navs have been fixed, you should be able to navigate from the stuck page now. If not, please petition again. (This is an automatic message).`n`nRegards %s",$session['user']['name']));
			invalidatedatacache("petition_counts");
			redirect("viewpetition.php?op=view&id=$nextid");
			break;
		default:
			$sql="SELECT author FROM ".DB::prefix("petitions")." WHERE petitionid='$id'";
			$result=DB::query($sql);
			$author=DB::fetch_assoc($result);
			debug($author);
			if (!$author) {
				output_notl("Error! Could not find that user!!");
				page_footer();
				return;
			}
			$author=$author['author'];
			$sql="UPDATE ".DB::prefix("accounts")." SET allowednavs='',specialinc='' WHERE acctid=$author";
			$result=DB::query($sql);
			$sql="UPDATE ".DB::prefix("accounts_everypage")." SET allowednavs='' WHERE acctid=$author";
			$result=DB::query($sql);
			invalidatedatacache("accounts/account_".$author);
            pt_insert(translate_inline("/me has fixed this users navs"));

            require_once 'lib/systemmail.php';

			systemmail($author,array("Your petition"),array("Your navs have been fixed, you should be able to navigate from the stuck page now. If not, please petition again. (This is an automatic message).`n`nRegards %s",$session['user']['name']));
			redirect("viewpetition.php?op=view&id=$id");
			break;

	}
	page_footer();
}

function pt_insert($text)
{
    $id = httpget('id');

    require_once 'lib/commentary.php';

    injectcommentary("pet-$id", '', $text);

	return;
}
