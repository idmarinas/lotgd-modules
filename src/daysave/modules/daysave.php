<?php

// v1.1 fixed a bug that caused a possible infinite newday loop when not logging out after using a newday
// V1.2 Fixes newday hook, added debug info by SexyCook
// V1.3 Added hook to jail
// V1.4 commented the debugs that were getting on my nerves, added an output for 0 days, due to translation difficulties.
// V1.5 Fixed the bug that gave new players the max amount of saved days
// V2.0 CMJ update - Donation Points functionality, World Map integration, Instant Buy option, Chronosphere integration

function daysave_getmoduleinfo()
{
    return [
        'name'     => 'Game Day Accumulation',
        'author'   => 'CavemanJoe, based on daysave.php by Exxar with fixes by SexyCook, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '3.1.0',
        'category' => 'General',
        'settings' => [
            'startdays'    => 'Number of game days with which to start a new player,int|2',
            'startslots'   => 'Number of game day slots to start,int|2',
            'buyslotcost'  => 'Players can buy an extra day slot in return for this many Donator Points,int|250',
            'fillslotcost' => 'Players have the option to fill up their days when buying a new day slot in exchange for this many Donator Points per day to be filled,int|10',
            'buydaycost'   => 'Players have the option to buy an Instant New Day at any time for this many Donator Points,int|25',
            'maxbuyday'    => 'Players can buy only this many Instant New Days per real Game Day,int|1',
        ],
        'prefs' => [
            'days'          => 'Current number of saved Game Days,int|0',
            'slots'         => 'Maximum number of saved Game Days,int|0',
            'instantbuys'   => 'Number of Instant New Days bought during this Game Day,int|0',
            'lastlognewday' => 'Next newday after logout,int|5',
            'initsetup'     => 'Player has been initially granted their starting settings,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function daysave_install()
{
    module_addhook('newday');
    module_addhook('newday-runonce');
    module_addhook('player-logout');
    module_addhook('village');
    module_addhook('shades');
    module_addhook('worldnav');

    return true;
}

function daysave_uninstall()
{
    return true;
}

function daysave_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'newday':
            $days          = get_module_pref('days');
            $slots         = get_module_pref('slots');
            $lastonnextday = get_module_pref('lastlognewday');
            $time          = gametimedetails();
            $timediff      = $time['gametime'] - $lastonnextday;

            if ($timediff > 86400)
            {
                $addition = \floor($timediff / 86400);
                $days += $addition;

                if ($days > $slots)
                {
                    $days = $slots;
                }

                if ($lastonnextday < 1)
                {
                    $days = 0;
                }

                set_module_pref('days', $days);
            }

            set_module_pref('lastlognewday', $time['tomorrow']);
        break;
        case 'newday-runonce':
            //reset all players' Instant Buys counter
            $repository = \Doctrine::getRepository('LotgdCore:Accounts');
            $query      = $repository->createQueryBuilder('u');
            $result     = $query->select('u.acctid')
                ->getQuery()
                ->getArrayResult()
            ;

            $query = $repository->getQueryBuilder();
            $query->update('LotgdCore:ModuleUserprefs', 'u')
                ->set('u.value', 0)
                ->where('u.modulename = :module AND u.setting = :setting AND u.userid IN (:user)')

                ->setParameter('module', 'daysave')
                ->setParameter('setting', 'instantbuys')
                ->setParameter('user', \array_map(function ($val)
                {
                    return $val['acctid'];
                }, $result))

                ->getQuery()
                ->execute()
            ;
        break;
        case 'player-logout':
            $details = gametimedetails();
            set_module_pref('lastlognewday', $details['tomorrow']);
        break;
        case 'village':
            \LotgdNavigation::addHeader('headers.fields');
            \LotgdNavigation::addNav('navigation.nav.day.saved', 'runmodule.php?module=daysave&op=start&return=village', ['textDomain' => 'module_daysave']);
        break;
        case 'shades':
            \LotgdNavigation::addHeader('navigation.category.new.day', ['textDomain' => 'module_daysave']);
            \LotgdNavigation::addNav('navigation.nav.day.saved', 'runmodule.php?module=daysave&op=start&return=shades', ['textDomain' => 'module_daysave']);
        break;
        case 'worldnav':
            \LotgdNavigation::addHeader('navigation.category.new.day', ['textDomain' => 'module_daysave']);
            \LotgdNavigation::addNav('navigation.nav.day.saved', 'runmodule.php?module=daysave&op=start&return=worldmapen', ['textDomain' => 'module_daysave']);
        break;
    }

    return $args;
}

function daysave_run()
{
    global $session;

    $op     = (string) \LotgdRequest::getQuery('op');
    $return = (string) \LotgdRequest::getQuery('return');

    //handle new players
    if ( ! get_module_pref('initsetup'))
    {
        set_module_pref('slots', get_module_setting('startslots'));
        set_module_pref('days', get_module_setting('startdays'));
        set_module_pref('initsetup', 1);
    }

    $params = [
        'textDomain'           => 'module_daysave',
        'days'                 => (int) get_module_pref('days'),
        'slots'                => (int) get_module_pref('slots'),
        'startDays'            => get_module_setting('startdays'),
        'buyDayCost'           => get_module_setting('buydaycost'),
        'buySlotCost'          => get_module_setting('buyslotcost'),
        'fillSlotCost'         => get_module_setting('fillslotcost'),
        'maxBuyDay'            => get_module_setting('maxbuyday'),
        'boughtToday'          => get_module_pref('instantbuys'),
        'donationPointsUnused' => $session['user']['donation'] - $session['user']['donationspent'],
    ];

    \LotgdResponse::pageStart('title', [], $params['textDomain']);

    //-- Change text domain for navigation
    \LotgdNavigation::setTextDomain($params['textDomain']);

    \LotgdNavigation::addHeader('navigation.category.chronofiddling');

    switch ($op)
    {
        case 'useday':
            $params['tpl'] = 'useday';
            --$params['days'];

            $params['days'] = \max(0, $params['days']);

            set_module_pref('days', $params['days']);

            \LotgdNavigation::addNav('navigation.nav.newday', 'newday.php');
        break;
        case 'buyday':
            $params['tpl'] = 'buyday';
            $params['donationPointsUnused'] -= $params['buyDayCost'];
            $session['user']['donationspent'] += $params['buyDayCost'];

            increment_module_pref('instantbuys');

            \LotgdNavigation::addNav('navigation.nav.newday', 'newday.php');
        break;
        case 'buyslot':
            $params['tpl'] = 'buyslot';
            $params['donationPointsUnused'] -= $params['buySlotCost'];
            $session['user']['donationspent'] += $params['buySlotCost'];

            increment_module_pref('days');
            increment_module_pref('slots');

            $params['days']  = get_module_pref('days');
            $params['slots'] = get_module_pref('slots');
            $empty           = $params['slots'] - $params['days'];

            $params['canFillSpheres'] = false;

            if ($empty && $params['donationPointsUnused'] >= $params['fillSlotCost'])
            {
                $params['canFillSpheres'] = true;
                \LotgdNavigation::addHeader('navigation.category.fill');

                for ($i = 1; $i <= $empty; ++$i)
                {
                    $cost = $i * $params['fillSlotCost'];

                    if ($params['donationPointsUnused'] >= $cost)
                    {
                        \LotgdNavigation::addNav('navigation.nav.fill', "runmodule.php?module=daysave&op=fillup&fill={$i}&return={$return}", [
                            'params' => ['n' => $i, 'cost' => $cost],
                        ]);
                    }
                }
            }
            \LotgdNavigation::addHeader('navigation.category.return');
            \LotgdNavigation::addNav('navigation.nav.return.menu', "runmodule.php?module=daysave&op=start&return={$return}");
        break;
        case 'fillup':
            $params['tpl'] = 'fillup';
            $fill          = (int) \LotgdRequest::getQuery('fill');

            increment_module_pref('days', $fill);

            $params['fillTotalCost'] = ($fill * $params['fillSlotCost']);
            $params['fill']          = $fill;
            $params['days']          = get_module_pref('days');
            $params['donationPointsUnused'] -= $params['fillTotalCost'];
            $session['user']['donationspent'] += $params['fillTotalCost'];

            \LotgdNavigation::addHeader('navigation.category.return');
            \LotgdNavigation::addNav('navigation.nav.return.menu', "runmodule.php?module=daysave&op=start&return={$return}");
        break;
        case 'start':
        default:
            $params['tpl'] = 'default';

            $nav  = $params['days'] ? 'navigation.nav.day.use' : 'navigation.nav.day.not.have';
            $link = $params['days'] ? 'runmodule.php?module=daysave&op=useday' : '';
            \LotgdNavigation::addNav($nav, $link);

            \LotgdNavigation::addHeader('navigation.category.donator');

            //-- Buy Day
            $nav = 'navigation.nav.donator.day.limit';

            if ($params['donationPointsUnused'] >= $params['buyDayCost'] && $params['boughtToday'] < $params['maxBuyDay'])
            {
                $nav = 'navigation.nav.donator.day.buy';
            }
            elseif ($params['donationPointsUnused'] < $params['buyDayCost'])
            {
                $nav = 'navigation.nav.donator.day.not.have';
            }

            $link = ($params['donationPointsUnused'] >= $params['buyDayCost'] && $params['boughtToday'] < $params['maxBuyDay'])
                ? 'runmodule.php?module=daysave&op=buyday'
                : ''
            ;
            \LotgdNavigation::addNav($nav, $link, ['params' => ['buyDayCost' => $params['buyDayCost']]]);

            //-- Buy slot
            $nav  = ($params['donationPointsUnused'] >= $params['buySlotCost']) ? 'navigation.nav.donator.slot.buy' : 'navigation.nav.donator.slot.not.have';
            $link = ($params['donationPointsUnused'] >= $params['buySlotCost']) ? "runmodule.php?module=daysave&op=buyslot&return={$return}" : '';
            \LotgdNavigation::addNav($nav, $link, ['params' => ['buySlotCost' => $params['buySlotCost']]]);

            \LotgdNavigation::addHeader('navigation.category.exit');

            if ('worldmapen' == $return)
            {
                \LotgdNavigation::addNav('navigation.nav.return.worldmapen', 'runmodule.php?module=worldmapen&op=continue');
            }
            else
            {
                \LotgdNavigation::villageNav();
            }
        break;
    }

    $params['empty'] = $params['slots'] - $params['days'];

    //-- Restore text domain for navigation
    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/daysave/run.twig', $params));

    \LotgdResponse::pageEnd();
}
