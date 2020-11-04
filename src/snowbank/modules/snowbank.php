<?php

// addnews ready
// mail ready
// translator ready

// modularized for Esoterra by Shannon Brown => SaucyWench -at- gmail -dot- com
// 8th October 2004
// ver 2.1 for Polareia Borealis (text changes only) 8th December 2004

function snowbank_getmoduleinfo()
{
    return [
        'name' => 'Snow Bank',
        'version' => '3.0.0',
        'author' => 'E Stevens, JT Traub, S Brown, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village',
        'download' => 'core_module',
        'settings' => [
            'bankloc' => 'Where does the bank appear,location|'.getsetting('villagename', LOCATION_FIELDS)
        ],
        'prefs' => [
            'giventoday' => 'Has the user given a gift today?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function snowbank_install()
{
    module_addhook('changesetting');
    module_addhook('newday');
    module_addhook('village');
    module_addhook('bank-text-domain');
    module_addhook('page-bank-tpl-params');

    return true;
}

function snowbank_uninstall()
{
    return true;
}

function snowbank_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'newday':
            set_module_pref('giventoday', 0, 'snowbank');
        break;
        case 'changesetting':
            if ('villagename' == $args['setting'] && $args['old'] == get_module_setting('bankloc'))
            {
                set_module_setting('bankloc', $args['new']);
            }
        break;
        case 'village':
            if ($session['user']['location'] == get_module_setting('bankloc'))
            {
                \LotgdNavigation::addHeader('headers.market');
                \LotgdNavigation::addNav('navs.bank', 'bank.php', ['remplace' => true, 'textDomain' => 'snowbank-bank-navigation']);
            }
        break;
        case 'bank-text-domain':
            if ($session['user']['location'] == get_module_setting('bankloc'))
            {
                $args['textDomain'] = 'snowbank-bank-bank';
                $args['textDomainNavigation'] = 'snowbank-bank-navigation';
            }
        break;
        case 'page-bank-tpl-params':
            if ($session['user']['location'] == get_module_setting('bankloc'))
            {
                $args['sex'] = $session['user']['sex'];

                \LotgdNavigation::addHeader('category.money');
                \LotgdNavigation::addNav('nav.gift', 'runmodule.php?module=snowbank&op=give');
            }
        break;
    }

    return $args;
}

function snowbank_run()
{
    global $session;

    $op = \LotgdHttp::getQuery('op');

    $giventoday = get_module_pref('giventoday');

    $textDomain = 'snowbank-module';

    \LotgdResponse::pageStart('title', [], $textDomain);

    \LotgdNavigation::addNav('nav.return.bank', 'bank.php');

    if ('give' == $op && 0 == $giventoday)
    {
        apply_buff('bank', [
            'name' => 'Generosity',
            'rounds' => 20,
            'defmod' => 1.02
        ]);

        set_module_pref('giventoday', 1);

        $params = [
            'textDomain' => $textDomain
        ];

        \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('snowbank/run/donate', $params));
    }
    elseif ('give' == $op && 1 == $giventoday)
    {
        $params = [
            'textDomain' => $textDomain
        ];

        \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('snowbank/run/donated', $params));
    }

    \LotgdResponse::pageEnd();
}
