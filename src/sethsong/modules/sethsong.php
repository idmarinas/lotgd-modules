<?php

//Seth's songs as a module
//Converted by Zach Lawson with some minor modification

/*
Version History:

Version 1.0.0 - First public release
Version 1.1.0 - Fixed a small bug that caused 2 "Return to the Inn" navs to show up
Version 1.2.0 Compatibility with IDMarinas version

*/

function sethsong_getmoduleinfo()
{
    return [
        'name'     => "Seth the Bard's Songs",
        'version'  => '3.0.0',
        'author'   => 'Eric Stevens, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Inn',
        'download' => 'core_module',
        'settings' => [
            "Seth the Bard's Songs,title",
            'bhploss' => 'Percent of hitpoints that can be lost when Seth burps,range,2,100,2|10',
            'shploss' => 'Percent of hitpoints that can be lost when a string breaks,range,2,100,2|20',
            'hpgain'  => 'Percent of max hitpoints that can be gained,range,2,100,2|20',
            //I realize adding 100% of max hitpoints or killing them when they go to Seth is a little outrageous, but might as well give admins the option
            'maxgems' => 'Most gems that can be found,int|1',
            'mingems' => 'Fewest gems that can be found,int|1',
            'Set these equal to each other for a fixed amount,note',
            'mingold'  => 'Minimum amount gold you can find,int|10',
            'maxgold'  => 'Maximum amount gold you can find,int|50',
            'goldloss' => 'Amount of gold that can be lost,int|5',
            "Warning: If a player's gold is less than this amount they loose nothin!,note",
            'visits' => 'How many times per day can a player listen to Seth,int|1',
        ],
        'prefs' => [
            "Seth the Bard's Songs,title",
            'been' => 'How many times have they listened Seth today,int|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function sethsong_install()
{
    module_addhook('inn');
    module_addhook('newday');

    return true;
}

function sethsong_uninstall()
{
    return true;
}

function sethsong_dohook($hookname, $args)
{
    switch ($hookname)
    {
        case 'inn':
            $bard = LotgdSetting::getSetting('bard', '`^Seth`0');
            $op   = \LotgdRequest::getQuery('op');

            if ('' == $op || 'strolldown' == $op || 'fleedragon' == $op)
            {
                \LotgdNavigation::addHeader('category.do', ['textDomain' => 'navigation_inn']);
                \LotgdNavigation::addNav('navigation.nav.listen', 'runmodule.php?module=sethsong', [
                    'textDomain' => 'module_sethsong',
                    'params'     => ['bard' => $bard],
                ]);
            }

        break;
        case 'newday':
            set_module_pref('been', 0);

        break;
        default: break;
    }

    return $args;
}

function sethsong_run()
{
    $visits     = get_module_setting('visits');
    $been       = get_module_pref('been');
    $iname      = LotgdSetting::getSetting('innname', LOCATION_INN);
    $textDomain = 'module_sethsong';

    \LotgdResponse::pageStart($iname, [], $textDomain);

    $params = [
        'textDomain' => $textDomain,
        'bard'       => LotgdSetting::getSetting('bard', '`^Seth`0'),
        'iname'      => $iname,
        'been'       => $been,
        'visits'     => $visits,
    ];

    // Short circuit out if we've heard enough
    if ($been >= $visits)
    {
        $params['tpl'] = 'default';
    }
    else
    {
        $params['tpl'] = 'sing';
        sethsong_sing($params);
    }

    \LotgdNavigation::addHeader('navigation.category.to', ['textDomain' => $textDomain]);
    \LotgdNavigation::addNav('navigation.nav.return', 'inn.php', ['textDomain' => $textDomain]);
    \LotgdNavigation::villageNav();

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/sethsong/run.twig', $params));

    \LotgdResponse::pageEnd();
}

function sethsong_sing(&$params)
{
    global $session;

    $mostgold      = get_module_setting('maxgold');
    $leastgold     = get_module_setting('mingold');
    $lgain         = get_module_setting('hpgain');
    $bloss         = get_module_setting('bhploss');
    $sloss         = get_module_setting('shploss');
    $gold          = get_module_setting('goldloss');
    $mostgems      = get_module_setting('maxgems');
    $leastgems     = get_module_setting('mingems');
    $been          = get_module_pref('been');
    $staminaSystem = is_module_active('staminasystem');

    ++$been;
    set_module_pref('been', $been);

    if ($staminaSystem)
    {
        require_once 'modules/staminasystem/lib/lib.php';
    }

    $params['staminaSystem'] = $staminaSystem;
    $params['barman']        = LotgdSetting::getSetting('barkeep', '`tCedrik`0');

    switch (\mt_rand(0, 16))
    {
        default:
        case 0:
            $params['case'] = 0;

            if ($staminaSystem)
            {
                addstamina(50000);
            }
            else
            {
                $session['user']['turns'] += 2;
                $session['user']['turns'] = \max(0, $session['user']['turns']);
            }

        break;
        case 1:
            $params['case'] = 1;
            // Since masters are now editable, pick a random one.
            $query = \Doctrine::createQueryBuilder();
            $name  = $query->select('u.creaturename')
                ->from('LotgdCore:Masters', 'u')
                ->orderBy('RAND()')
                ->setMaxResults(1)

                ->getQuery()
                ->getSingleScalarResult()
            ;

            $name             = $name ?: 'MightyE';
            $params['master'] = $name;

            if ($staminaSystem)
            {
                addstamina(25000);
            }
            else
            {
                ++$session['user']['turns'];
                $session['user']['turns'] = \max(0, $session['user']['turns']);
            }

        break;
        case 2:
            $params['case']       = 2;
            $params['playerName'] = $session['user']['name'];

            if ($staminaSystem)
            {
                addstamina(25000);
            }
            else
            {
                ++$session['user']['turns'];
                $session['user']['turns'] = \max(0, $session['user']['turns']);
            }

        break;
        case 3:
            $params['case'] = 3;

            $params['goldGain'] = e_rand($leastgold, $mostgold);
            $session['user']['gold'] += $params['goldGain'];
            \LotgdLog::debug("found {$params['goldGain']} gold near Seth");

        break;
        case 4:
            $params['case'] = 4;

            $session['user']['hitpoints'] = \round(\max($session['user']['maxhitpoints'], $session['user']['hitpoints']) * (($lgain / 100) + 1), 0);

        break;
        case 5:
            $params['case'] = 5;

            if ($staminaSystem)
            {
                removestamina(25000);
            }
            else
            {
                --$session['user']['turns'];
                $session['user']['turns'] = \max(0, $session['user']['turns']);
            }

        break;
        case 6:
            $params['case'] = 6;

            if ($staminaSystem)
            {
                addstamina(25000);
            }
            else
            {
                ++$session['user']['turns'];
                $session['user']['turns'] = \max(0, $session['user']['turns']);
            }

        break;
        case 7:
            $params['case'] = 7;

            $session['user']['hitpoints'] -= \round($session['user']['maxhitpoints'] * ($bloss / 100), 0);

            $session['user']['hitpoints'] = \max(1, $session['user']['hitpoints']);

        break;
        case 8:
            $params['case'] = 8;

            $params['goldLost'] = 0;

            if ($session['user']['gold'] >= $gold)
            {
                $params['goldLost'] = 0;

                $session['user']['gold'] -= $gold;
                \LotgdLog::debug("lost {$gold} gold to Seth");
            }

        break;
        case 9:
            $params['case']     = 9;
            $params['gemsGain'] = e_rand($leastgems, $mostgems);

            $session['user']['gems'] += $gems;

            \LotgdLog::debug("got {$params['gemsGain']} gem\\(s\\) from Seth");

        break;
        case 10:
            $params['case'] = 10;

            $session['user']['hitpoints'] = \max($session['user']['hitpoints'], $session['user']['maxhitpoints']);

        break;
        case 11:
            $params['case'] = 11;

            if ($staminaSystem)
            {
                removestamina(25000);
            }
            else
            {
                --$session['uset']['turns'];
                $session['user']['turns'] = \max(0, $session['user']['turns']);
            }

        break;
        case 12:
            $params['case'] = 12;

            $session['user']['hitpoints'] = \max($session['user']['hitpoints'], $session['user']['maxhitpoints']);

        break;
        case 13:
            $params['case'] = 13;

            if ($staminaSystem)
            {
                addstamina(25000);
            }
            else
            {
                ++$session['uset']['turns'];
                $session['user']['turns'] = \max(0, $session['user']['turns']);
            }

        break;
        case 14:
            $params['case'] = 14;
            $session['user']['hitpoints'] -= \round($session['user']['maxhitpoints'] * ($sloss / 100), 0);

            if ($session['user']['hitpoints'] < 1)
            {
                $session['user']['hitpoints'] = 1;
            }

        break;
        case 15:
            $params['case']  = 15;
            $params['armor'] = $session['user']['armor'];

        break;
        case 16:
            $params['case'] = 16;
            --$session['user']['charm'];
            $params['ugly'] = ($session['user']['charm'] < 0);

        break;
    }
}
