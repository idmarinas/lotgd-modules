<?php

// 1.0.1 - Better for translations
function namecolor_getmoduleinfo()
{
    return [
        'name' => 'Name Colorization',
        'author' => 'Eric Stevens, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version' => '2.0.0',
        'download' => 'core_module',
        'category' => 'Lodge',
        'settings' => [
            'Name Colorization Module Settings,title',
            'initialpoints' => 'How many points will the first color change cost?,int|300',
            'extrapoints' => 'How many points will subsequent color changes cost?,int|25',
            'maxcolors' => 'How many color changes are allowed in names?,int|10',
            'bold' => 'Allow bold?,bool|1',
            'italics' => 'Allow italics?,bool|1',
        ],
        'prefs' => [
            'Name Colorization User Preferences,title',
            'boughtbefore' => 'Has user bought a color change before?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function namecolor_install()
{
    module_addhook('lodge');
    module_addhook('pointsdesc');

    return true;
}
function namecolor_uninstall()
{
    return true;
}

function namecolor_dohook($hookname, $args)
{
    global $session;

    $textDomain = 'module-namecolor';

    switch ($hookname)
    {
        case 'pointsdesc':
            $args[] = ['points.description', [
                'initial' => get_module_setting('initialpoints'),
                'extra' => get_module_setting('extrapoints')
            ], $textDomain];
        break;
        case 'lodge':
            $cost = get_module_setting('initialpoints');

            if (get_module_pref('boughtbefore'))
            {
                $cost = get_module_setting('extrapoints');
            }

            \LotgdNavigation::addNav('navigation.nav.change', 'runmodule.php?module=namecolor&op=namechange', [
                'textDomain' => $textDomain,
                'params' => ['cost' => $cost]
            ]);
        break;
        default: break;
    }

    return $args;
}

function namecolor_run()
{
    require_once 'lib/names.php';

    global $session;

    $rebuy = get_module_pref('boughtbefore');
    $cost = get_module_setting($rebuy ? 'extrapoints' : 'initialpoints');
    $pointsavailable = $session['user']['donation'] - $session['user']['donationspent'];

    $op = \LotgdRequest::getQuery('op');

    $textDomain = 'module-namecolor';

    \LotgdResponse::pageStart('title', [], $textDomain);

    $params = [
        'textDomain' => $textDomain,
        'reBuy' => $rebuy,
        'cost' => $cost,
        'pointsAvailable' => $pointsavailable,
        'extraPoints' => get_module_setting('extrapoints'),
        'regName' => get_player_basename()
    ];

    switch ($op)
    {
        case 'changename':
            $params['tpl'] = 'changename';

            $session['user']['donationspent'] += $cost;
            set_module_pref('boughtbefore', 1);

            $fromname = $session['user']['name'];
            $newname = change_player_name(rawurldecode(\LotgdRequest::getQuery('name')));
            $session['user']['name'] = $newname;

            addnews('news.changed', [
                'from' => $fromname,
                'new' => $session['user']['name']
            ], $textDomain);

            $params['name'] = $session['user']['name'];

            modulehook('namechange', []);

            \LotgdNavigation::addNav('navigation.nav.return', 'lodge.php', ['textDomain' => $textDomain]);
        break;
        case 'namepreview':
            $params['tpl'] = 'namepreview';

            $newname = (string) \LotgdRequest::getPost('newname');

            if (! get_module_setting('bold'))
            {
                $newname = str_replace(['`b', '´b'], '', $newname);
            }

            if (! get_module_setting('italics'))
            {
                $newname = str_replace(['`i', '´i'], '', $newname);
            }
            //-- Deleted center code and other
            $newname = preg_replace('/[`´][ncHw]/', '', $newname);

            $comp1 = strtolower(\LotgdSanitize::fullSanitize($params['regName']));
            $comp2 = strtolower(\LotgdSanitize::fullSanitize($newname));

            $err = 0;

            if ($comp1 != $comp2)
            {
                $err = 1;
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('flash.message.error.not.equal', ['name' => $newname], $textDomain));
            }

            if (strlen($newname) > 30)
            {
                $err = 1;
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('flash.message.error.long', [], $textDomain));
            }

            $colorCount = substr_count($newname, '`');
            $max = (int) get_module_setting('maxcolors');

            if ($colorCount > $max)
            {
                $err = 1;
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('flash.message.error.count', ['colorCount' => $colorCount, 'max' => $max], $textDomain));
            }

            $params['newName'] = $newname;

            if (! $err)
            {
                \LotgdNavigation::addHeader('navigation.category.confirm', ['textDomain' => $textDomain]);
                \LotgdNavigation::addNav('navigation.nav.yes', 'runmodule.php?module=namecolor&op=changename&name='.rawurlencode($newname), ['textDomain' => $textDomain]);
                \LotgdNavigation::addNav('navigation.nav.no', 'runmodule.php?module=namecolor&op=namechange', ['textDomain' => $textDomain]);
            }
            else
            {
                \LotgdNavigation::addNav('navigation.nav.return', 'lodge.php', ['textDomain' => $textDomain]);
            }

            $params['error'] = $err;
        break;
        case 'namechange':
        default:
            $params['tpl'] = 'default';

            \LotgdNavigation::addNav('navigation.nav.return', 'lodge.php', ['textDomain' => $textDomain]);
        break;
    }

    \LotgdResponse::pageAddContent(LotgdTheme::renderModuleTemplate('namecolor/run.twig', $params));

    \LotgdResponse::pageEnd();
}
