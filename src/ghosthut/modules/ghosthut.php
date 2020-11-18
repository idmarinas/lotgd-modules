<?php

// addnews ready
// mail ready
// translator ready

/* Ghost Town Villager's Hut */
/* ver 1.0 by Shannon Brown => SaucyWench -at- gmail -dot- com */
/* 21st Sept 2004 */

function ghosthut_getmoduleinfo()
{
    return [
        'name' => "Ghost Town Villager's Hut",
        'version' => '2.0.0',
        'author' => 'Shannon Brown, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village',
        'download' => 'core_module',
        'settings' => [
            "Villager's Hut - Settings,title",
            'ghosthutloc' => 'Where does the hut appear,location|'.getsetting('villagename', LOCATION_FIELDS)
        ],
        'prefs' => [
            "Villager's Hut - User Preferences,title",
            'eattoday' => 'How much has the user eaten today?,int|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function ghosthut_install()
{
    module_addhook('changesetting');
    module_addhook('newday');
    module_addhook('village');

    return true;
}

function ghosthut_uninstall()
{
    return true;
}

function ghosthut_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'changesetting':
            // these said goblinhotel - whoops!
            if ('villagename' == $args['setting'] && $args['old'] == get_module_setting('ghosthutloc'))
            {
                set_module_setting('ghosthutloc', $args['new']);
            }
        break;
        case 'newday':
            set_module_pref('eattoday', 0);
        break;
        case 'village':
            if ($session['user']['location'] == get_module_setting('ghosthutloc'))
            {
                \LotgdNavigation::addHeader('headers.tavern');
                \LotgdNavigation::addNav('navigation.nav.hut', 'runmodule.php?module=ghosthut', ['textDomain' => 'module-ghosthut']);
            }
        break;
        default: break;
    }

    return $args;
}

function ghosthut_run()
{
    global $session;

    $op = \LotgdRequest::getQuery('op');
    $eattoday = get_module_pref('eattoday');

    $turn = mt_rand(1, 8);

    $textDomain = 'module-ghosthut';

    \LotgdResponse::pageStart('title', [], $textDomain);

    $params = [
        'textDomain' => $textDomain
    ];

    \LotgdNavigation::villageNav();

    if ($eattoday >= 3)
    {
        $params['tpl'] = 'full';

        $turn = 2;
    }
    elseif ('' == $op)
    {
        $params['tpl'] = 'default';
        $turn = 2;

        \LotgdNavigation::addHeader('navigation.category.eat', ['textDomain' => $textDomain]);
        \LotgdNavigation::addNav('navigation.nav.eat1', 'runmodule.php?module=ghosthut&op=1', ['textDomain' => $textDomain]);
        \LotgdNavigation::addNav('navigation.nav.eat2', 'runmodule.php?module=ghosthut&op=2', ['textDomain' => $textDomain]);
        \LotgdNavigation::addNav('navigation.nav.eat3', 'runmodule.php?module=ghosthut&op=3', ['textDomain' => $textDomain]);
        \LotgdNavigation::addNav('navigation.nav.eat4', 'runmodule.php?module=ghosthut&op=4', ['textDomain' => $textDomain]);
    }
    elseif ('3' == $op)
    {
        $params['tpl'] = 'eat3';

        $session['user']['hitpoints'] = ($session['user']['hitpoints'] * 0.85);
        $session['user']['hitpoints'] = max(1, $session['user']['hitpoints']);

        $eattoday += 3;
        $turn = 2;
        set_module_pref('eattoday', $eattoday);
    }
    elseif ('4' == $op)
    {
        $params['tpl'] = 'eat4';

        $session['user']['hitpoints'] = ($session['user']['hitpoints'] * 0.65);
        $session['user']['hitpoints'] = max(1, $session['user']['hitpoints']);

        if ($session['user']['charm'] > 0)
        {
            $session['user']['charm']--;
        }
        $eattoday += 4;
        $turn = 2;
        set_module_pref('eattoday', $eattoday);
    }
    elseif ('1' == $op)
    {
        $params['tpl'] = 'eat1';

        $session['user']['hitpoints'] = max($session['user']['hitpoints'] + 3, $session['user']['hitpoints'] * 1.02);
        $eattoday++;
        set_module_pref('eattoday', $eattoday);

        if ($eattoday > 0 && $eattoday < 3)
        {
            \LotgdNavigation::addNav('navigation.nav.return', 'runmodule.php?module=ghosthut', ['textDomain' => $textDomain]);
        }
    }
    elseif ('2' == $op)
    {
        $params['tpl'] = 'eat2';
        $session['user']['hitpoints'] = max($session['user']['hitpoints'] + 5, $session['user']['hitpoints'] * 1.03);
        $eattoday += 2;
        set_module_pref('eattoday', $eattoday);

        if ($eattoday > 0 && $eattoday < 3)
        {
            \LotgdNavigation::addNav('navigation.nav.return', 'runmodule.php?module=ghosthut', ['textDomain' => $textDomain]);
        }
    }

    $params['turn'] = $turn;

    if (1 == $turn)
    {
        $session['user']['turns'] += 2;
    }

    \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('ghosthut/run.twig', $params));

    \LotgdResponse::pageEnd();
}
