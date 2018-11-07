<?php

// addnews ready
// mail ready
// translator ready

function topwebgames_getmoduleinfo()
{
    return [
        'name' => 'Top Web Games',
        'author' => 'JT Traub',
        'category' => 'Administrative',
        'version' => '1.1.0',
        'download' => 'core_module',
        'allowanonymous' => true,
        'override_forced_nav' => true,
        'settings' => [
            'Top Web Games Settings,title',
            'id' => 'Top Web Games ID,int|0',
            'hours' => 'Offset to Top Web Games servers,int|3',
        ],
        'prefs' => [
            'Top Web Games User Preferences,title',
            'lastvote' => 'When did user last vote|0000-00-00 00:00:00',
            'voted' => 'Did user vote this week?,bool|0',
        ],
    ];
}

function topwebgames_install()
{
    module_addhook('charstats');

    $sql = 'DESCRIBE '.DB::prefix('accounts');
    $result = DB::query($sql);

    while ($row = DB::fetch_assoc($result))
    {
        if ('lastwebvote' == $row['Field'])
        {
            $sql = 'SELECT lastwebvote,acctid FROM '.DB::prefix('accounts')." WHERE lastwebvote>'0000-00-00 00:00:00'";
            $result1 = DB::query($sql);
            debug('Migrating last web vote time.`n');

            while ($row1 = DB::fetch_assoc($result1))
            {
                $sql = 'INSERT INTO '.DB::prefix('module_userprefs')." (modulename,setting,userid,value) VALUES ('topwebgames','lastvote',{$row1['acctid']},{$row1['lastwebvote']})";

                DB::query($sql);
            }//end while
            debug('Dropping last web vote column from the user table.`n');
            $sql = 'ALTER TABLE '.DB::prefix('accounts').' DROP lastwebvote';
            DB::query($sql);
            //drop it from the user's session too.
            unset($session['user']['lastwebvote']);
        }//end if
    } // end while

    return true;
}

function topwebgames_uninstall()
{
    debug('Uninstalling module.');

    return true;
}

function topwebgames_dohook($hookname, $args)
{
    global $session;

    $id = get_module_setting('id');

    if (! $id)
    {
        return $args;
    }

    if ('charstats' == $hookname)
    {
        require_once 'lib/pullurl.php';

        $counts = datacache('topwebcounts', 900, true);

        if (! $counts)
        {
            $counts = '';
            $c = @pullurl("http://www.topwebgames.com/games/votes.js?id=$id");
            $r = @pullurl("http://www.topwebgames.com/games/placement.js?id=$id");

            if (false !== $c)
            {
                $c = join($c, '');

                if (preg_match("/\\.write\\('([0-9]+)'\\)/", $c, $matches))
                {
                    $counts .= sprintf(translate('`&Votes this week: `^%s`0`n'), $matches[1]);
                }
            }
            else
            {
                $counts = translate('`&Votes this week: `^TWG Error`n');
            }

            if (false !== $r)
            {
                $r = join($r, '');

                if (preg_match("/\\.write\\('([0-9]+)'\\)/", $r, $matches))
                {
                    $counts .= sprintf(translate('`&Rank this week: `@%s`0`n'), $matches[1]);
                }
            }
            else
            {
                $counts .= translate('`&Rank this week: `@TWG Error`n');
            }

            updatedatacache('topwebcounts', $counts, true);
        }
        addcharstat('Top Web Games');
        $prev = datacache('topwebprev', 900, true);

        if (! $prev)
        {
            $when = @pullurl('http://www.topwebgames.com/games/countdown.js');

            if (false !== $when)
            {
                $when = join($when, '');

                if (preg_match('/Next reset: (.+ [AP]M)/', $when, $matches))
                {
                    $prev = strtotime($matches[1].' -7 days');
                    //in case the web call fails this time around, we'll still cache the old value for 10 minutes.
                    //So we need to track what the old value was independant of the datacache library.
                    set_module_setting('topwebprev', $prev);
                }
            }

            if (false === $prev)
            {
                //this'll happen when the pullurl call failed, we fetch the last known value so that
                //we don't end up trying to do a pullurl every page hit.
                $prev = get_module_setting('topwebprev');
            }
            updatedatacache('tpowebprev', $prev, true);
        }
        $l = get_module_pref('lastvote');

        if (! $l)
        {
            $l = '0000-00-00 00:00:00';
        }
        $last = strtotime($l);
        $img = "<img border='0' src='images/topwebgames-88x31.gif'>";

        if ($prev && $last < $prev)
        {
            $acct = $session['user']['acctid'];
            $url = "http://www.topwebgames.com/in.asp?id=$id&acctid=$acct&alwaysreward=1";
            $vote = translate('`^Vote now! `&Gain `%1 Gem`0');
            $vote = "<a href='$url' target='_blank' onClick=\"Lotgd.embed(this)\">$img<br>".$vote.'</a>';
            set_module_pref('voted', 0);
        }
        else
        {
            $vote = "$img<br>`@Already voted this week`0";
        }
        addnav('Top Web Games');
        $val = "`c$vote<br>$counts`c";
        addcharstat('Top Web Games', $val);
    }

    return $args;
}

function topwebgames_run()
{
    $op = httpget('op');

    if ('twgvote' != $op)
    {
        do_forced_nav(false, false);
    }
    $id = httppost('acctid');
    $votecounted = httppost('votecounted');

    if (! $id)
    {
        $id = httpget('acctid');
    }

    if (! $id)
    {
        $id = 1;
    }

    $lastvote = get_module_pref('lastvote', 'topwebgames', $id);
    // if (!get_module_pref("voted", "topwebgames", $id))
    if ($votecounted)
    {
        $dt = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' + '.get_module_setting('hours').' hours'));
        $sql = 'UPDATE '.DB::prefix('accounts')." SET gems=gems+1 WHERE acctid=$id";
        DB::query($sql);
        set_module_pref('voted', 1, 'topwebgames', $id);
        set_module_pref('lastvote', $dt, 'topwebgames', $id);
        debuglog('gained 1 gem for topwebgames', 0, $id);
        invalidatedatacache('topwebcounts', true);
        echo 'OK';
    }
    else
    {
        echo 'Already voted';
    }
}
