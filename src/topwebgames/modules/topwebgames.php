<?php

// addnews ready
// mail ready
// translator ready

function topwebgames_getmoduleinfo()
{
    return [
        'name'                => 'Top Web Games',
        'author'              => 'JT Traub, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category'            => 'Administrative',
        'version'             => '3.0.0',
        'download'            => 'core_module',
        'allowanonymous'      => true,
        'override_forced_nav' => true,
        'settings'            => [
            'Top Web Games Settings,title',
            'id'    => 'Top Web Games ID,int|0',
            'hours' => 'Offset to Top Web Games servers,int|3',
        ],
        'prefs' => [
            'Top Web Games User Preferences,title',
            'lastvote' => 'When did user last vote|0000-00-00 00:00:00',
            'voted'    => 'Did user vote this week?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function topwebgames_install()
{
    module_addhook('everyfooter');

    return true;
}

function topwebgames_uninstall()
{
    LotgdResponse::pageDebug('Uninstalling module.');

    return true;
}

function topwebgames_dohook($hookname, $args)
{
    global $session;

    $id = (int) get_module_setting('id');

    if (empty($id))
    {
        return $args;
    }

    if ('everyfooter' == $hookname)
    {
        $cache = LotgdKernel::get('cache.app');
        $item  = $cache->getItem('topwebcounts');

        if ( ! $item->isHit())
        {
            /** @var \Symfony\Component\HttpClient\Response\CurlResponse $c */
            // $c = LotgdKernel::get("lotgd_http_client")->request('GET', "http://www.topwebgames.com/games/votes.js?id={$id}");
            /** @var \Symfony\Component\HttpClient\Response\CurlResponse $r */
            $r = LotgdKernel::get("lotgd_http_client")->request('GET', "http://www.topwebgames.com/games/placement.js?id={$id}");

            $votes = '??';

            // if (\preg_match("/\\.write\\('(\\d+)'\\)/", $c->getContent(), $matches))
            // {
            //     $votes = $matches[1];
            // }

            $rank = '??';

            if (\preg_match("/\\.write\\('(\\d+)'\\)/", $r->getContent(), $matches))
            {
                $rank = $matches[1];
            }

            $counts = [
                'votes' => $votes,
                'rank'  => $rank,
            ];

            $item->set($counts);
            $cache->save($item);
        }
        $counts = $item->get();
        $item   = $cache->getItem('topwebprev');

        if ( ! $item->isHit())
        {
            /** @var \Symfony\Component\HttpClient\Response\CurlResponse $when */
            $when = LotgdKernel::get("lotgd_http_client")->request('GET', 'http://www.topwebgames.com/games/countdown.js');

            if (\preg_match('/Next reset: (.+ [AP]M)/', $when->getContent(), $matches))
            {
                $prev = \strtotime($matches[1].' -7 days');
                //in case the web call fails this time around, we'll still cache the old value for 10 minutes.
                //So we need to track what the old value was independant of the datacache library.
                set_module_setting('topwebprev', $prev);
            }

            if (false === $prev)
            {
                //this'll happen when the pullurl call failed, we fetch the last known value so that
                //we don't end up trying to do a pullurl every page hit.
                $prev = get_module_setting('topwebprev');
            }

            $item->set($prev);
            $cache->save($item);
        }

        $prev = $item->get();

        $l = get_module_pref('lastvote');
        $l = $l ?: '0000-00-00 00:00:00';

        $last = \strtotime($l);

        $canVote = false;

        if ($prev && $last < $prev)
        {
            $canVote = true;
            set_module_pref('voted', 0);
        }

        $params = [
            'textDomain' => 'module_topwebgames',
            'rank'       => $counts['rank'],
            'votes'      => $counts['votes'],
            'serverName' => LotgdSetting::getSetting('servername'),
            'canVote'    => $canVote,
            'acctId'     => $session['user']['acctid'],
            'id'         => $id,
        ];

        $args['paypal'] = $args['paypal'] ?? '';
        $args['paypal'] .= LotgdTheme::render('@module/topwebgames_everyfooter.twig', $params);
    }

    return $args;
}

function topwebgames_run()
{
    $op = LotgdRequest::getQuery('op');

    if ('twgvote' != $op)
    {
        do_forced_nav(false, false);
    }

    $id          = LotgdRequest::getPost('acctid');
    $votecounted = LotgdRequest::getPost('votecounted');

    if ( ! $id)
    {
        $id = LotgdRequest::getQuery('acctid');
    }

    $id = \max(1, $id);

    if ($votecounted)
    {
        $dt = \date('Y-m-d H:i:s', \strtotime(\date('Y-m-d H:i:s').' + '.get_module_setting('hours').' hours'));

        $repository = Doctrine::getRepository('LotgdCore:Avatar');
        $entity     = $repository->find($id);

        if ($entity)
        {
            $entity->setGems($entity->getGems() + 1);

            Doctrine::persist($entity);
            Doctrine::flush();
        }

        set_module_pref('voted', 1, 'topwebgames', $id);
        set_module_pref('lastvote', $dt, 'topwebgames', $id);
        LotgdLog::debug('gained 1 gem for topwebgames', 0, $id);
        LotgdKernel::get('cache.app')->delete('topwebcounts', true);

        echo 'OK';
    }
    else
    {
        echo 'Already voted';
    }
}
