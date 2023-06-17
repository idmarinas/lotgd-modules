<?php

// modularized for Esoterra by Shannon Brown => SaucyWench -at- gmail -dot- com
// 8th October 2004

function bloodbank_getmoduleinfo()
{
    return [
        'name'     => 'Blood Bank',
        'version'  => '4.0.0',
        'author'   => 'E Stevens, JT Traub, S Brown, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village',
        'download' => 'core_module',
        'settings' => [
            'bankloc' => 'Where does the bank appear,location|'.LotgdSetting::getSetting('villagename', LOCATION_FIELDS),
        ],
        'prefs' => [
            'giventoday' => 'Has the user given blood today?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function bloodbank_install()
{
    module_addhook('changesetting');
    module_addhook('newday');
    module_addhook('bank-text-domain');
    module_addhook('page-bank-tpl-params');
    module_addhook('village');

    return true;
}

function bloodbank_uninstall()
{
    return true;
}

function bloodbank_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'newday':
            set_module_pref('giventoday', 0, 'bloodbank');
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
                LotgdNavigation::addHeader('headers.market');
                LotgdNavigation::addNav('navs.bank', 'bank.php', ['remplace' => true, 'textDomain' => 'blookbank_bank_navigation']);
            }
        break;
        case 'bank-text-domain':
            if ($session['user']['location'] == get_module_setting('bankloc'))
            {
                $args['textDomain']           = 'blookbank_bank';
                $args['textDomainNavigation'] = 'blookbank_bank_navigation';
            }
        break;
        case 'page-bank-tpl-params':
            if ($session['user']['location'] == get_module_setting('bankloc'))
            {
                $args['sex'] = $session['user']['sex'];

                LotgdNavigation::addHeader('category.money');
                LotgdNavigation::addNav('nav.blood', 'runmodule.php?module=bloodbank&op=give', ['textDomain' => 'blookbank_bank_navigation']);
            }
        break;
    }

    return $args;
}

function bloodbank_run()
{
    global $session;

    $op = LotgdRequest::getQuery('op');

    $giventoday = get_module_pref('giventoday');
    $textDomain = 'blookbank_module';

    LotgdResponse::pageStart('title', [], $textDomain);

    LotgdNavigation::addNav('nav.return.bank', 'bank.php');

    if ('give' == $op && 0 == $giventoday)
    {
        LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('bloodbank', [
            'name'   => LotgdTranslator::t('donation.buff.name', [], $textDomain),
            'rounds' => 20,
            'defmod' => 1.02,
        ]);

        set_module_pref('giventoday', 1);

        $params = [
            'textDomain' => $textDomain,
        ];

        LotgdResponse::pageAddContent(LotgdTheme::render('@module/bloodbank/run/donate.twig', $params));
    }
    elseif ('give' == $op && 1 == $giventoday)
    {
        $params = [
            'textDomain' => $textDomain,
        ];

        LotgdResponse::pageAddContent(LotgdTheme::render('@module/bloodbank/run/donated.twig', $params));
    }

    LotgdResponse::pageEnd();
}
