<?php

// translator ready
// mail ready
// addnews ready
function findring_getmoduleinfo()
{
    return [
        'name' => 'Find Ring',
        'version' => '2.0.0',
        'author' => 'Atrus, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function findring_install()
{
    module_addeventhook('travel', 'return 75;');
    module_addeventhook('forest', 'return 80;');

    return true;
}

function findring_uninstall()
{
    return true;
}

function findring_dohook($hookname, $args)
{
    return $args;
}

function findring_runevent($type, $link)
{
    global $session;

    $from = $link;
    $op = (string) \LotgdRequest::getQuery('op');
    $session['user']['specialinc'] = 'module:findring';

    $params = [
        'textDomain' => 'module-findring'
    ];

    //-- Change text domain for navigation
    \LotgdNavigation::setTextDomain($params['textDomain']);

    if ('pickup' == $op)
    {
        $params['tpl'] = 'pickup';

        $dk = max(1, $session['user']['dragonkills']);

        $dkchance = max(5, (ceil($dk / 5)));

        if (e_rand(0, $dkchance) <= 1)
        {
            $params['chance'] = true;

            $session['user']['charm']++;
            $session['user']['specialinc'] = '';
        }
        else
        {
            $params['chance'] = false;

            $amt = round($session['user']['hitpoints'] * 0.05, 0);
            $session['user']['hitpoints'] -= $amt;
            $session['user']['hitpoints'] = max(1, $session['user']['hitpoints']);

            $session['user']['specialinc'] = '';
        }
    }
    elseif ('no' == $op)
    {
        $params['tpl'] = 'leave';

        $session['user']['specialinc'] = '';
    }
    else
    {
        $params['tpl'] = 'default';

        \LotgdNavigation::addHeader('navigation.category.ring');
        \LotgdNavigation::addNav('navigation.nav.pick', "{$from}op=pickup");
        \LotgdNavigation::addNav('navigation.nav.leave', "{$from}op=no");
    }

    //-- Restore text domain for navigation
    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('findring/run.twig', $params));
}

function findring_run()
{
}
