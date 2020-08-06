<?php

// Version history
// 1.0 - initial version by JT Traub
// 1.1 - Better for translations
function titlechange_getmoduleinfo()
{
    return [
        'name' => 'Title Change',
        'author' => 'JT Traub, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version' => '2.0.0',
        'download' => 'core_module',
        'category' => 'Lodge',
        'settings' => [
            'Title Change Module Settings,title',
            'initialpoints' => 'How many donator points needed to get first title change?,int|5000',
            'extrapoints' => 'How many additional donator points needed for subsequent title changes?,int|25',
            'bold' => 'Allow bold?,bool|1',
            'italics' => 'Allow italics?,bool|1',
            'blank' => 'Allow blank titles?,bool|1',
            'spaceinname' => 'Allow spaces in custom titles?,bool|1',
        ],
        'prefs' => [
            'Title Change User Preferences,title',
            'timespurchased' => 'How many title changes have been bought?,int|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function titlechange_install()
{
    module_addhook('lodge');
    module_addhook('pointsdesc');

    return true;
}
function titlechange_uninstall()
{
    return true;
}

function titlechange_dohook($hookname, $args)
{
    global $session;

    $textDomain = 'module-titlechange';

    switch ($hookname)
    {
        case 'pointsdesc':
            $args[] = ['points.description', [
                'initial' => get_module_setting('initialpoints'),
                'extra' => get_module_setting('extrapoints')
            ], $textDomain];
        break;
        case 'lodge':
            // If they have less than what they need just ignore them
            $times = get_module_pref('timespurchased');

            if (get_module_setting('initialpoints') + ($times * get_module_setting('extrapoints')) > $session['user']['donation'])
            {
                break;
            }

            $cost = get_module_setting('initialpoints');

            if (get_module_pref('timespurchased'))
            {
                $cost = get_module_setting('extrapoints');
            }

            \LotgdNavigation::addNav('navigation.nav.change', 'runmodule.php?module=titlechange&op=titlechange', [
                'textDomain' => $textDomain,
                'params' => ['cost' => $cost]
            ]);
        break;
        default: break;
    }

    return $args;
}

function titlechange_run()
{
    require_once 'lib/names.php';

    global $session;

    $op = \LotgdHttp::getQuery('op');

    $textDomain = 'module-titlechange';

    page_header('title', [], $textDomain);

    $params = [
        'textDomain' => $textDomain
    ];

    \LotgdNavigation::addNav('navigation.nav.return', 'lodge.php', ['textDomain' => $textDomain]);

    switch ($op)
    {
        case 'changetitle':
            $params['tpl'] = 'changetitle';

            $ntitle = rawurldecode(LotgdHttp::getQuery('newname'));
            $fromname = $session['user']['name'];
            $newname = change_player_ctitle($ntitle);
            $session['user']['ctitle'] = $ntitle;
            $session['user']['name'] = $newname;

            addnews('news.changed', [
                'from' => $fromname,
                'new' => $session['user']['name']
            ], $textDomain);

            if (get_module_setting('timespurchased'))
            {
                $cost = get_module_setting('extrapoints');
                debuglog("bought another custom title for $cost points");
            }
            else
            {
                $cost = get_module_setting('initialpoints');
                debuglog("bought first custom title for $cost points");
            }

            $session['user']['donationspent'] += $cost;

            set_module_pref('timespurchased', get_module_pref('timespurchased') + 1);
            modulehook('namechange', []);
        break;
        case 'titlepreview':
            $params['tpl'] = 'titlepreview';

            $ntitle = rawurldecode(\LotgdHttp::getPost('newname'));

            if ('' == $ntitle && ! get_module_setting('blank'))
            {
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('flash.message.not.empty', [], $textDomain));

                return redirect('runmodule.php?module=titlechange&op=titlechange&err=1');
            }

            if (! get_module_setting('bold'))
            {
                $ntitle = str_replace(['`b', '´b'], '', $ntitle);
            }

            if (! get_module_setting('italics'))
            {
                $ntitle = str_replace(['`i', '´i'], '', $ntitle);
            }
            $ntitle = get_module_setting('spaceinname') ? $ntitle : preg_replace('/ /', '', $ntitle);
            $ntitle = \LotgdSanitize::htmlSanitize($ntitle);
            $ntitle = preg_replace('/[`´][ncHw]/', '', $ntitle);

            $params['nname'] = get_player_basename();
            $params['ntitle'] = $ntitle;

            \LotgdNavigation::addHeader('navigation.category.confirm', ['textDomain' => $textDomain]);
            \LotgdNavigation::addNav('navigation.nav.yes', 'runmodule.php?module=titlechange&op=changetitle&newname='.rawurlencode($ntitle), ['textDomain' => $textDomain]);
            \LotgdNavigation::addNav('navigation.nav.no', 'runmodule.php?module=titlechange&op=titlechange', ['textDomain' => $textDomain]);
        break;
        case 'titlechange':
        default:
            $params['tpl'] = 'default';

            $otitle = get_player_title();

            if ('`0' == $otitle)
            {
                $otitle = '';
            }

            $params['otitle'] = $otitle;
        break;
    }

    rawoutput(LotgdTheme::renderModuleTemplate('titlechange/run.twig', $params));

    page_footer();
}
