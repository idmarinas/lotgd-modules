<?php

// addnews ready
// mail ready
// translator ready

function topwebgames_getmoduleinfo()
{
    return [
        'name' => 'Top Web Games',
        'author' => 'JT Traub, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Administrative',
        'version' => '2.0.0',
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
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function topwebgames_install()
{
    module_addhook('everyfooter');

    return true;
}

function topwebgames_uninstall()
{
    debug('Uninstalling module.');

    return true;
}

function topwebgames_dohook($hookname, $args)
{
    global $session, $html;

    $id = (int) get_module_setting('id');

    if (! $id)
    {
        return $args;
    }

    if ('everyfooter' == $hookname)
    {
        require_once 'lib/pullurl.php';

        $counts = datacache('topwebcounts', 900, true);

        if (! is_array($counts) || empty($counts))
        {
            $c = @pullurl("http://www.topwebgames.com/games/votes.js?id={$id}");
            $r = @pullurl("http://www.topwebgames.com/games/placement.js?id={$id}");

            $votes = 'error';
            if (false !== $c)
            {
                $c = join($c, '');

                if (preg_match("/\\.write\\('([0-9]+)'\\)/", $c, $matches))
                {
                    $votes = $matches[1];
                }
            }

            $rank = 'error';
            if (false !== $r)
            {
                $r = join($r, '');

                if (preg_match("/\\.write\\('([0-9]+)'\\)/", $r, $matches))
                {
                    $rank = $matches[1];
                }
            }

            $counts = [
                'votes' => $votes,
                'rank' => $rank
            ];

            updatedatacache('topwebcounts', $counts, true);
        }

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

            updatedatacache('topwebprev', $prev, true);
        }

        $l = get_module_pref('lastvote');
        $l = $l ?: '0000-00-00 00:00:00';

        $last = strtotime($l);

        $canVote = false;
        if ($prev && $last < $prev)
        {
            $canVote = true;
            set_module_pref('voted', 0);
        }

        $params = [
            'textDomain' => 'module-topwebgames',
            'rank' => $counts['rank'],
            'votes' => $counts['votes'],
            'serverName' => getsetting('servername'),
            'canVote' => $canVote,
            'acctId' => $session['user']['acctid'],
            'id' => $id
        ];

        $html['paypal'] = $html['paypal'] ?? '';
        $html['paypal'] .= \LotgdTheme::renderModuleTemplate('topwebgames/dohook/everyfooter.twig', $params);
    }

    return $args;
}

function topwebgames_run()
{
    $op = \LotgdHttp::getQuery('op');

    if ('twgvote' != $op)
    {
        do_forced_nav(false, false);
    }

    $id = \LotgdHttp::getPost('acctid');
    $votecounted = \LotgdHttp::getPost('votecounted');

    if (! $id)
    {
        $id = \LotgdHttp::getQuery('acctid');
    }

    $id = max(1, $id);

    if ($votecounted)
    {
        $dt = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' + '.get_module_setting('hours').' hours'));

        $repository = \Doctrine::getRepository('LotgdCore:Characters');
        $entity = $repository->find($id);

        if ($entity)
        {
            $entity->setGems($entity->getGems() + 1);

            \Doctrine::persist($entity);
            \Doctrine::flush();
        }

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
