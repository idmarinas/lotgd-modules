<?php

/* Ice Town for Dec/Jan */
/* ver 1.0 9th Nov 2004 */
/* Shannon Brown => SaucyWench -at- gmail -dot- com */

function icetown_getmoduleinfo()
{
    return [
        'name'     => 'Ice Town',
        'version'  => '2.1.0',
        'author'   => 'Shannon Brown, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village',
        'download' => 'core_module',
        'settings' => [
            'Ice Town Settings,title',
            'villagename' => 'Name for the ice town|Polareia Borealis',
            'allowtravel' => "Allow 'standard' travel to town?,bool|1",
        ],
        'prefs' => [
            'Ice Town User Preferences,title',
            'allow' => 'Is player allowed in?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function icetown_install()
{
    module_addhook('village-text-domain');
    module_addhook('page-village-tpl-params');
    module_addhook('travel');
    module_addhook('validlocation');
    module_addhook('moderate-comment-sections');
    module_addhook('changesetting');
    module_addhook('pvpwin');
    module_addhook('pvploss');

    return true;
}

function icetown_uninstall()
{
    global $session;

    $vname = getsetting('villagename', LOCATION_FIELDS);
    $gname = get_module_setting('villagename');

    try
    {
        $charactersRepository = \Doctrine::getRepository('LotgdCore:Characters');

        //-- Updated location
        $query = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Characters', 'u')
            ->set('u.location', ':new')
            ->where('u.location = :old')

            ->setParameter('old', $gname)
            ->setParameter('new', $vname)

            ->getQuery()
            ->execute()
        ;

        if ($session['user']['location'] == $gname)
        {
            $session['user']['location'] = $vname;
        }
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);

        return false;
    }

    return true;
}

function icetown_dohook($hookname, $args)
{
    global $session, $resline;

    $city = get_module_setting('villagename');

    switch ($hookname)
    {
        case 'pvpwin':
            if ($session['user']['location'] == $city)
            {
                $args['handled'] = true;

                \LotgdLog::addNews('news.pvp.win', [
                    'playerName'   => $session['user']['name'],
                    'creatureName' => $args['badguy']['creaturename'],
                    'location'     => $args['badguy']['location'],
                ], 'icetown_module');
            }
        break;
        case 'pvploss':
            if ($session['user']['location'] == $city)
            {
                require_once 'lib/taunt.php';

                $args['handled'] = true;

                \LotgdLog::addNews('deathmessage', [
                    'deathmessage' => [
                        'deathmessage' => 'news.pvp.defeated',
                        'params'       => [
                            'playerName'   => $session['user']['name'],
                            'creatureName' => $args['badguy']['creaturename'],
                            'location'     => $args['badguy']['location'],
                        ],
                        'textDomain' => 'ghosttown-module',
                    ],
                    'taunt' => \LotgdTool::selectTaunt(),
                ], '');
            }
        break;
        case 'travel':
            $allow  = get_module_pref('allow') || get_module_setting('allowtravel');
            $hotkey = \substr($city, 0, 1);
            $ccity  = \urlencode($city);

            //-- Change text domain for navigation
            \LotgdNavigation::setTextDomain('cities-navigation');

            // Esoterra is always dangerous travel.
            if ($session['user']['location'] != $city && $allow)
            {
                \LotgdNavigation::addHeader('headers.travel.dangerous');
                \LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}&d=1", [
                    'params' => ['key' => $hotkey, 'city' => $city],
                ]);
            }

            if ($session['user']['superuser'] & SU_EDIT_USERS && $allow)
            {
                \LotgdNavigation::addHeader('headers.superuser');
                \LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}&su=1", [
                    'params' => ['key' => $hotkey, 'city' => $city],
                ]);
            }

            //-- Restore text domain for navigation
            \LotgdNavigation::setTextDomain();
        break;
        case 'changesetting':
            // Ignore anything other than villagename setting changes
            if ('villagename' == $args['setting'] && 'icetown' == $args['module'])
            {
                if ($session['user']['location'] == $args['old'])
                {
                    $session['user']['location'] = $args['new'];
                }

                $charactersRepository = \Doctrine::getRepository('LotgdCore:Characters');

                $query = $charactersRepository->getQueryBuilder();
                $query->update('LotgdCore:Characters', 'u')
                    ->set('u.location', ':new')
                    ->where('u.location = :old')

                    ->setParameter('old', $args['old'])
                    ->setParameter('new', $args['new'])

                    ->getQuery()
                    ->execute()
                ;
            }
        break;
        case 'validlocation':
            $canvisit = 0;

            if (is_module_active('icecaravan') && get_module_setting('canvisit', 'icecaravan'))
            {
                $canvisit = 1;
            }

            if (get_module_pref('allow') || get_module_setting('allowtravel'))
            {
                $canvisit = 1;
            }

            if ( ! $canvisit && ( ! isset($arg['all']) || ! $args['all']))
            {
                break;
            }

            if (is_module_active('cities'))
            {
                $args[$city] = 'village-icetown';
            }
        break;
        case 'moderate-comment-sections':
            if (is_module_active('cities'))
            {
                $args['village-icetown'] = \LotgdTranslator::t('moderate', ['city' => $city], 'icetown_module');
            }
        break;
        case 'village-text-domain':
            if ($session['user']['location'] == $city)
            {
                $args['textDomain']           = 'icetown_village';
                $args['textDomainNavigation'] = 'icetown_village_navigation';

                \LotgdNavigation::blockHideLink('lodge.php');
                \LotgdNavigation::blockHideLink('weapons.php');
                \LotgdNavigation::blockHideLink('armor.php');
                \LotgdNavigation::blockHideLink('clan.php');
                \LotgdNavigation::blockHideLink('pvp.php');
                \LotgdNavigation::blockHideLink('forest.php');
                \LotgdNavigation::blockHideLink('gardens.php');
                \LotgdNavigation::blockHideLink('gypsy.php');
                \LotgdNavigation::blockHideLink('bank.php');

                $allow = get_module_pref('allow') || get_module_setting('allowtravel');

                if ( ! $allow)
                {
                    blockmodule('cities');
                }

                blockmodule('tynan');
                blockmodule('spookygold');
                blockmodule('scavenge');
                blockmodule('caravan');
                // why would you see someone outside the clan halls that don't
                // exist in this village
                blockmodule('clantrees');
            }
        break;
        case 'page-village-tpl-params':
            if ($session['user']['location'] == $city)
            {
                $args['village']           = $city;
                $args['commentarySection'] = 'village-icetown'; //-- Commentary section
                $args['defaced']           = get_module_setting('defacedname');

                //-- Newest player in realm
                $args['newestplayer'] = 0;
                $args['newestname']   = '';

                \LotgdNavigation::addHeader('headers.gate');
                \LotgdNavigation::addNav('navs.pvp', 'pvp.php?campsite=1');
            }
        break;
    }

    return $args;
}

function icetown_run()
{
}
