<?php

// translator ready
// addnews ready
// mail ready

function frosty_getmoduleinfo()
{
    return [
        'name'     => 'Frosty the Snowman',
        'version'  => '2.1.0',
        'author'   => 'Talisman, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village Specials',
        'download' => 'core_module',
        'settings' => [
            'Frosty the Snowman Settings, title',
            'rawchance' => 'Raw chance of encountering Frosty,range,5,100,1|50',
            'frostyloc' => 'Where does the Frosty appear,location|'.getsetting('villagename', LOCATION_FIELDS),
        ],
        'prefs' => [
            'Frosty the Snowman User Prefs, title',
            'seentoday' => 'Has the player rebuilt Frosty today,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function frosty_test()
{
    global $session;

    $city = get_module_setting('frostyloc', 'frosty');

    if ($city != $session['user']['location'])
    {
        return 0;
    }

    return get_module_setting('rawchance', 'frosty');
}

function frosty_install()
{
    module_addeventhook('village', 'require_once("modules/frosty.php"); return frosty_test();');
    module_addhook('newday');
}

function frosty_uninstall()
{
    return true;
}

function frosty_dohook($hookname, $args)
{
    if ('newday' == $hookname)
    {
        set_module_pref('seentoday', 0);
    }

    return $args;
}

function frosty_runevent($type)
{
    global $session;

    $session['user']['specialinc'] = 'module:frosty';
    $op                            = \LotgdRequest::getQuery('op');

    $textDomain = 'module_frosty';

    $params = [
        'textDomain' => $textDomain,
    ];

    switch ($op)
    {
        case 'leave':
            $params['tpl']  = 'leave';
            $params['rand'] = \mt_rand(1, 4);

            switch ($params['rand'])
            {
                case 1:
                    $cur                          = $session['user']['hitpoints'];
                    $lhitpoints                   = \round($cur * .3);
                    $session['user']['hitpoints'] = ($cur - $lhitpoints);

                    $session['user']['hitpoints'] = \max(1, $session['user']['hitpoints']);

                    $loss = $cur - $session['user']['hitpoints'];

                    $params['loseHp'] = $loss;
                break;
                case 2:
                    $session['user']['charm']--;
                break;
                case 3:
                    $loss = \round($session['user']['gold'] * .4);
                    $session['user']['gold'] -= $loss;
                    $session['user']['gold'] = \max(0, $session['user']['gold']);

                    $params['gold'] = $loss;

                    debuglog("lost {$loss} gold ignoring frosty");
                break;
                default:
                break;
            }
            $session['user']['specialinc'] = '';
        break;

        case 'ignore':
            $params['tpl'] = 'ignore';

            if (1 == \mt_rand(1, 4))
            {
                $cur                          = $session['user']['hitpoints'];
                $lhitpoints                   = \round($cur * .1);
                $session['user']['hitpoints'] = ($cur - $lhitpoints);
                $session['user']['hitpoints'] = \max(1, $session['user']['hitpoints']);

                $loss = $cur - $session['user']['hitpoints'];

                $params['loseHp'] = $loss;
            }

            $session['user']['specialinc'] = '';
        break;

        case 'help':
            $params['tpl']           = 'help';
            $params['staminaSystem'] = is_module_active('staminaSystem');
            $params['rand']          = \mt_rand(1, 3);

            if ($params['staminaSystem'])
            {
                require_once 'modules/staminasystem/lib/lib.php';
                removestamina(25000);
            }
            else
            {
                ++$session['user']['turns'];
            }

            set_module_pref('seentoday', 1);
            $session['user']['specialinc'] = '';

            switch ($params['rand'])
            {
                default:
                case 1:
                    if ($params['staminaSystem'])
                    {
                        removestamina(50000);
                    }
                    else
                    {
                        $session['user']['turns'] += 2;
                    }
                break;
                case 2:
                    debuglog('got a gem helping frosty');
                    ++$session['user']['gems'];
                break;
                case 3:
                    $fgold = (20 * $session['user']['level']);
                    debuglog("found {$fgold} gold helping frosty");
                    $session['user']['gold'] += $fgold;
                    $params['gold'] = $fgold;
                break;
            }
        break;

        case 'talk':
            $params['tpl'] = 'talk';

            \LotgdNavigation::addNav('navigation.nav.help', 'village.php?op=help', ['textDomain' => $textDomain]);
            \LotgdNavigation::addNav('navigation.nav.leave', 'village.php?op=leave', ['textDomain' => $textDomain]);
        break;

        default:
            $params['tpl']       = 'default';
            $params['seenToday'] = get_module_pref('seentoday');

            if ($params['seenToday'])
            {
                $session['user']['specialinc'] = '';
            }
            else
            {
                \LotgdNavigation::addHeader('navigation.category.do', ['textDomain' => $textDomain]);
                \LotgdNavigation::addNav('navigation.nav.talk', 'village.php?op=talk', ['textDomain' => $textDomain]);
                \LotgdNavigation::addNav('navigation.nav.ignore', 'village.php?op=ignore', ['textDomain' => $textDomain]);
            }
        break;
    }

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/frosty/runevent.twig', $params));
}

function frosty_run()
{
}
