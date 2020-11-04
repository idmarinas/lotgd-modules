<?php

// translator ready
// addnews ready
// mail ready

/* Offering Special  */
/* ver 1.0 by Shannon Brown => SaucyWench -at- gmail -dot- com */
/* 20th Nov 2004 */

// default settings add average 6 charm points per 10 gems spent

function offering_getmoduleinfo()
{
    return [
        'name' => 'Offering Special',
        'version' => '2.0.0',
        'author' => 'Shannon Brown, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village Specials',
        'download' => 'core_module',
        'prefs' => [
            'Offering Special User Preferences,title',
            'seen' => 'Seen special today?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
        ],
    ];
}

function offering_install()
{
    module_addhook('newday');
    module_addeventhook('village', '$seen=get_module_pref("seen", "offering");return ($seen>5?0:10);');

    return true;
}

function offering_uninstall()
{
    return true;
}

function offering_dohook($hookname, $args)
{
    global $session;

    if ('newday' == $hookname)
    {
        set_module_pref('seen', 0);
    }

    return $args;
}

function offering_runevent($type)
{
    global $session;

    $session['user']['specialinc'] = 'module:offering';

    $seen = get_module_pref('seen');
    $amt = round(max(1, $session['user']['dragonkills'] * 10) * $session['user']['level'] * (max(1, 5000 - $session['user']['maxhitpoints'])) / 20000);

    $textDomain = 'module-offering';

    $params = [
        'textDomain' => $textDomain,
        'seen' => $seen,
        'amount' => $amt,
        'deathOverlord' => getsetting('deathoverlord', '`$Ramius`0')
    ];

    $op = \LotgdHttp::getQuery('op');

    \LotgdNavigation::setTextDomain($textDomain);

    if ('' == $op)
    {
        $params['tpl'] = 'default';

        $seen++;
        set_module_pref('seen', $seen);

        \LotgdNavigation::addNav('navigation.nav.default.shop', 'village.php?op=shop', ['params' => ['amount' => $amt]]);
        \LotgdNavigation::addNav('navigation.nav.default.nope', 'village.php?op=nope');
    }
    elseif ('nope' == $op)
    {
        $params['tpl'] = 'nope';

        $session['user']['specialinc'] = '';
    }
    elseif ($session['user']['gold'] < $amt)
    {
        $params['tpl'] = 'gold';

        $session['user']['specialinc'] = '';
    }
    else
    {
        $params['tpl'] = 'shop';

        $session['user']['deathpower'] += 15;

        if ($session['user']['dragonkills'] > 30)
        {
            $session['user']['deathpower'] -= 5;
        }

        $session['user']['gold'] -= $amt;
    }

    \LotgdNavigation::setTextDomain();

    $params['seen'] = $seen;

    \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('offering/runevent.twig', $params));
}
