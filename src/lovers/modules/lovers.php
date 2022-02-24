<?php

//should we move charm here?
//should we move marriedto here?

function lovers_getmoduleinfo()
{
    return [
        'name'     => 'Violet and Seth Lovers',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '4.0.0',
        'category' => 'Inn',
        'download' => 'core_module',
        'prefs'    => [
            'Lover Module User Preferences,title',
            'seenlover' => 'Visited Lover Today?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=6.0.0|Need a version equal or greater than 6.0.0 IDMarinas Edition',
        ],
    ];
}

function lovers_install()
{
    module_addhook('newday');
    module_addhook('inn');

    return true;
}

function lovers_uninstall()
{
    return true;
}

function lovers_dohook($hookname, $args)
{
    global $session;

    require_once 'lib/partner.php';

    $textDomain = 'module_lovers';

    $partner = LotgdTool::getPartner();

    switch ($hookname)
    {
        case 'newday':
            set_module_pref('seenlover', 0);

            if (4294967295 == $session['user']['marriedto'])
            {
                $dk = $session['user']['dragonkills'];

                // 0.7 seemed to be a perfect balance of no loss of charm.
                // 1.0 was too much.
                $dk        = \max(1, \round(.85 * \sqrt($dk), 0));
                $charmloss = e_rand(1, $dk);
                $session['user']['charm'] -= $charmloss;

                if ($session['user']['charm'] <= 0)
                {
                    LotgdLog::addNews('news.note', ['partner' => $partner, 'playerName' => $session['user']['name']], $textDomain);
                    $session['user']['marriedto'] = 0;
                    $session['user']['charm']     = 0;
                }

                $args['includeTemplatesPost']['@module/lovers/dohook/newday.twig'] = [
                    'textDomain' => $textDomain,
                    'charmLoss'  => $charmloss,
                    'partner'    => $partner,
                    'playerName' => $session['user']['name'],
                ];
            }
        break;
        case 'inn':
            LotgdNavigation::addHeader('category.do', ['textDomain' => 'navigation_inn']);

            LotgdNavigation::addNav('navigation.nav.flirt.with', 'runmodule.php?module=lovers&op=flirt', [
                'textDomain' => $textDomain,
                'params'     => ['partner' => $partner],
            ]);

            $session['user']['prefs']['sexuality'] = $session['user']['prefs']['sexuality'] ?? ! $session['user']['sex'];

            if (SEX_MALE != $session['user']['prefs']['sexuality'])
            {
                LotgdNavigation::addNav('navigation.nav.chat.bard.chat', 'runmodule.php?module=lovers&op=chat', [
                    'textDomain' => $textDomain,
                    'params'     => ['name' => LotgdSetting::getSetting('bard', '`^Seth`0')],
                ]);
            }
            else
            {
                LotgdNavigation::addNav('navigation.nav.chat.barmaid.chat', 'runmodule.php?module=lovers&op=chat', [
                    'textDomain' => $textDomain,
                    'params'     => ['name' => LotgdSetting::getSetting('barmaid', '`%Violet`0')],
                ]);
            }
        break;
        default: break;
    }

    return $args;
}

function lovers_run()
{
    global $session;

    require_once 'lib/partner.php';

    $textDomain = 'module_lovers';

    $params = [
        'textDomain' => $textDomain,
        'playerName' => $session['user']['name'],
        'innName'    => LotgdSetting::getSetting('innname', LOCATION_INN),
        'barkeep'    => LotgdSetting::getSetting('barkeep', '`tCedrik`0'),
        'bard'       => LotgdSetting::getSetting('bard', '`^Seth`0'),
        'barmaid'    => LotgdSetting::getSetting('barmaid', '`%Violet`0'),
        'partner'    => LotgdTool::getPartner(),
    ];

    LotgdResponse::pageStart($params['innName'], [], $textDomain);

    LotgdNavigation::setTextDomain($textDomain);

    switch (LotgdRequest::getQuery('op'))
    {
        case 'flirt':
            if (SEX_MALE != $session['user']['prefs']['sexuality'])
            {
                $params['tpl'] = 'flirt-barmaid';

                require_once 'modules/lovers/lovers_barmaid.php';
            }
            else
            {
                $params['tpl'] = 'flirt-bard';

                require_once 'modules/lovers/lovers_bard.php';
            }
        break;
        case 'chat':
            if (SEX_MALE != $session['user']['prefs']['sexuality'])
            {
                $params['tpl'] = 'chat-bard';

                require_once 'modules/lovers/lovers_chat_bard.php';
            }
            else
            {
                $params['tpl'] = 'chat-barmaid';

                require_once 'modules/lovers/lovers_chat_barmaid.php';
            }
        break;
        default: break;
    }

    LotgdNavigation::addHeader('navigation.category.return');
    LotgdNavigation::addNav('navigation.nav.inn', 'inn.php');
    LotgdNavigation::villageNav();

    LotgdNavigation::setTextDomain();

    LotgdResponse::pageAddContent(LotgdTheme::render('@module/lovers/run.twig', $params));

    LotgdResponse::pageEnd();
}

function lovers_getbuff()
{
    global $session;

    require_once 'lib/partner.php';

    $partner = LotgdTool::getPartner();

    return [
        'name'     => LotgdTranslator::t('buff.name', [], $textDomain),
        'rounds'   => 60,
        'wearoff'  => LotgdTranslator::t('buff.wearoff', ['partner' => $partner], $textDomain),
        'defmod'   => 1.2,
        'roundmsg' => LotgdTranslator::t('buff.roundmsg', [], $textDomain),
        'schema'   => 'module_lovers',
    ];
}
