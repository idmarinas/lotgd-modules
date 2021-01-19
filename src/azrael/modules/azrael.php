<?php

// addnews ready
// mail ready
// translator ready

/* Azrael the Spook */
/* ver 1.0 by Shannon Brown => SaucyWench -at- gmail -dot- com */
/* 8th Sept 2004 */

function azrael_getmoduleinfo()
{
    return [
        'name'     => 'Azrael the Spook',
        'version'  => '2.1.0',
        'author'   => 'Shannon Brown, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village Specials',
        'download' => 'core_module',
        'settings' => [
            'Azrael the Spook - Settings,title',
            'azraelloc' => 'Where does the Azrael appear,location|'.getsetting('villagename', LOCATION_FIELDS),
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function azrael_install()
{
    module_addhook('changesetting');
    module_addeventhook('village', 'require_once "modules/azrael.php"; return azrael_test();');

    return true;
}

function azrael_uninstall()
{
    return true;
}

function azrael_dohook($hookname, $args)
{
    global $session;

    if ('changesetting' == $hookname && 'villagename' == $args['setting'] && $args['old'] == get_module_setting('azraelloc'))
    {
        set_module_setting('azraelloc', $args['new']);
    }

    return $args;
}

function azrael_test()
{
    global $session;

    if ($session['user']['location'] == get_module_setting('azraelloc', 'azrael'))
    {
        return 100;
    }

    return 0;
}

function azrael_runevent($type)
{
    global $session;

    require_once 'lib/partner.php';

    $session['user']['specialinc'] = '';
    $from                          = 'village.php?';
    $city                          = get_module_setting('azraelloc');
    $op                            = \LotgdRequest::getQuery('op');

    $textDomain = 'module_azrael';

    $params = [
        'textDomain' => $textDomain,
        'city'       => $city,
        'partner'    => $partner,
    ];

    switch ($op)
    {
        case 'ignore':
            $params['tpl'] = 'ignore';

            if ($session['user']['charm'] > 0)
            {
                --$session['user']['charm'];
            }

            if ($session['user']['hitpoints'] > 1)
            {
                $session['user']['hitpoints'] = \ceil($session['user']['hitpoints'] * 0.8);
            }
        break;

        case 'trick':
            $params['tpl'] = 'trick';

            $params['bad'] = \mt_rand(1, 5);

            if (1 == $params['bad'])
            {
                if ($session['user']['charm'] > 1)
                {
                    $session['user']['charm'] -= 2;
                    $params['lostCharm'] = true;
                }

                // Aww heck, let's have the buff survive new day.
                apply_buff('azrael', [
                    'name'          => \LotgdTranslator::t('buff.trick.name', [], $textDomain),
                    'rounds'        => 60,
                    'wearoff'       => \LotgdTranslator::t('buff.trick.wearoff', [], $textDomain),
                    'defmod'        => 1.03,
                    'survivenewday' => 1,
                    'roundmsg'      => \LotgdTranslator::t('buff.trick.roundmsg', [], $textDomain),
                ]);
            }
            elseif (2 == $params['bad'])
            {
                $takegold = \max(\min($session['user']['level'] * 3, $session['user']['gold']), 0);
                $takegems = \max(\min(\ceil(($session['user']['level'] + 1) / 5), $session['user']['gems']), 0);

                $params['takeGold'] = $takegold;
                $params['takeGems'] = $takegems;

                $session['user']['gems'] -= $takegems;
                $session['user']['gold'] -= $takegold;

                \LotgdResponse::pageDebug("Lost {$takegold} gold and {$takegems} gems to the trick or treat kid.");
            }
            else
            {
                if ($session['user']['charm'] > 0)
                {
                    --$session['user']['charm'];
                }
            }
        break;

        case 'treat':
            $params['tpl'] = 'treat';

            --$session['user']['gold'];
            apply_buff('azrael', [
                'name'          => \LotgdTranslator::t('buff.treat.name', [], $textDomain),
                'rounds'        => 60,
                'wearoff'       => \LotgdTranslator::t('buff.treat.wearoff', [], $textDomain),
                'atkmod'        => 1.03,
                'survivenewday' => 1,
                'roundmsg'      => \LotgdTranslator::t('buff.treat.roundmsg', [], $textDomain),
            ]);
        break;

        default:
            $params['tpl'] = 'default';

            $session['user']['specialinc'] = 'module:azrael';

            if ($city == get_module_setting('villagename', 'ghosttown'))
            {
                $params['ghosttown'] = true;
            }
            \LotgdNavigation::addHeader('navigation.category.action', ['textDomain' => $textDomain]);

            if ($session['user']['gold'] > 0)
            {
                $params['canPay'] = true;
                \LotgdNavigation::addNav('navigation.nav.treat', $from.'op=treat', ['textDomain' => $textDomain]);
            }

            \LotgdNavigation::addNav('navigation.nav.trick', $from.'op=trick', ['textDomain' => $textDomain]);
            \LotgdNavigation::addNav('navigation.nav.ignore', $from.'op=ignore', ['textDomain' => $textDomain]);
        break;
    }

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/azrael/runevent.twig', $params));
}
