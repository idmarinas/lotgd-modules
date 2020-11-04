<?php

// translator ready
// addnews ready
// mail ready

function racehuman_getmoduleinfo()
{
    return [
        'name' => 'Race - Human',
        'version' => '2.0.0',
        'author' => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Races',
        'download' => 'core_module',
        'settings' => [
            'Human Race Settings,title',
            'villagename' => 'Name for the human village|Romar',
            'minedeathchance' => 'Chance for Humans to die in the mine,range,0,100,1|90',
            'bonus' => 'How many extra forest fights for humans?,range,1,3,1|2',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function racehuman_install()
{
    //-- Upgrade from previous version
    try
    {
        $charactersRepository = \Doctrine::getRepository('LotgdCore:Characters');

        //-- Name of race
        $query = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Characters', 'u')
            ->set('u.race', ':new')
            ->where('u.race = :old')

            ->setParameter('old', 'Human')
            ->setParameter('new', 'racehuman-module')

            ->getQuery()
            ->execute()
        ;

        //-- Section of commentary
        $query = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Commentary', 'u')
            ->set('u.section', ':new')
            ->where('u.section = :old')

            ->setParameter('old', 'village-Human')
            ->setParameter('new', 'village-racehuman-module')

            ->getQuery()
            ->execute()
        ;
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);

        return false;
    }

    module_addhook('chooserace');
    module_addhook('setrace');
    module_addhook('newday');
    module_addhook('village-text-domain');
    module_addhook('page-village-tpl-params');
    module_addhook('stables-text-domain');
    module_addhook('travel');
    module_addhook('validlocation');
    module_addhook('validforestloc');
    module_addhook('moderate-comment-sections');
    module_addhook('changesetting');
    module_addhook('raceminedeath');
    module_addhook('stablelocs');
    module_addhook('racenames');

    return true;
}

function racehuman_uninstall()
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

        //-- Updated race name
        $query = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Characters', 'u')
            ->set('u.race', '')
            ->where('u.race = :race')

            ->setParameter('race', 'racehuman-module')

            ->getQuery()
            ->execute()
        ;

        if ('racehuman-module' == $session['user']['race'])
        {
            $session['user']['race'] = RACE_UNKNOWN;
        }
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);

        return false;
    }

    return true;
}

function racehuman_dohook($hookname, $args)
{
    //yeah, the $resline thing is a hack.  Sorry, not sure of a better way
    // to handle this.
    // Pass it as an arg?
    global $session,$resline;
    $city = get_module_setting('villagename');
    $race = 'racehuman-module'; //-- Now race is a textDomain for translator

    \LotgdNavigation::setTextDomain($race);

    switch ($hookname)
    {
        case 'racenames':
            $args[$race] = \LotgdTranslator::t('character.racename', [], $race);
        break;
        case 'raceminedeath':
            if ($session['user']['race'] == $race)
            {
                $args['chance'] = get_module_setting('minedeathchance');
            }
        break;
        case 'changesetting':
            // Ignore anything other than villagename setting changes
            if ('villagename' == $args['setting'] && 'racehuman' == $args['module'])
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

                if (is_module_active('cities'))
                {
                    $moduleUserPrefsRepository = \Doctrine::getRepository('LotgdCore:ModuleUserprefs');
                    $query = $moduleUserPrefsRepository->getQueryBuilder();
                    $query->update('LotgdCore:ModuleUserprefs', 'u')
                        ->set('u.value', ':new')
                        ->where('u.modulename = :cities AND settings = :home AND u.value = :old')

                        ->setParameter('old', $args['old'])
                        ->setParameter('new', $args['new'])
                        ->setParameter('cities', 'cities')
                        ->setParameter('home', 'homecity')

                        ->getQuery()
                        ->execute()
                    ;
                }
            }
        break;
        case 'chooserace':
            \LotgdNavigation::addNav('character.racename', "newday.php?setrace={$race}{$resline}");

            $params = [
                'city' => $city,
                'race' => $race,
                'resLine' => $resline
            ];

            \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('racehuman/dohook/chooserace.twig', $params));
        break;
        case 'setrace':
            if ($session['user']['race'] == $race)
            {
                \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('racehuman/dohook/setrace.twig', [
                    'bonus' => (int) get_module_setting('bonus')
                ]));

                if (is_module_active('cities'))
                {
                    if (0 == $session['user']['dragonkills'] && 0 == $session['user']['age'])
                    {
                        //new farmthing, set them to wandering around this city.
                        set_module_setting("newest-$city", $session['user']['acctid'], 'cities');
                    }
                    set_module_pref('homecity', $city, 'cities');

                    if (0 == $session['user']['age'])
                    {
                        $session['user']['location'] = $city;
                    }
                }
            }
        break;
        case 'newday':
            if ($session['user']['race'] == $race)
            {
                racehuman_checkcity();

                $bonus = (int) get_module_setting('bonus');

                if (is_module_active('staminasystem'))
                {
                    require_once 'modules/staminasystem/lib/lib.php';

                    $stamina = $bonus * 25000;
                    addstamina($stamina);
                    $args['turnstoday'] .= ", Race (human): $stamina stamina";
                }
                else
                {
                    $args['turnstoday'] .= ", Race (human): $bonus";
                    $session['user']['turns'] += $bonus;
                }

                $args['includeTemplatesPost']['module/racehuman/dohook/newday.twig'] = [
                    'bonus' => $bonus,
                    'staminaSystem' => is_module_active('staminasystem')
                ];
            }
        break;
        case 'validforestloc':
        case 'validlocation':
            if (is_module_active('cities'))
            {
                $args[$city] = "village-$race";
            }
        break;
        case 'moderate-comment-sections':
            if (is_module_active('cities'))
            {
                $args["village-$race"] = \LotgdTranslator::t('locs.moderate', ['city' => $city], $race);
            }
        break;
        case 'travel':
            $capital = getsetting('villagename', LOCATION_FIELDS);
            $hotkey = substr($city, 0, 1);
            $ccity = urlencode($city);

            //-- Change text domain for navigation
            \LotgdNavigation::setTextDomain('cities-navigation');

            if ($session['user']['location'] == $capital)
            {
                \LotgdNavigation::addHeader('headers.travel.safer');
                \LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}", [
                    'params' => ['key' => $hotkey, 'city' => $city]
                ]);
            }
            elseif ($session['user']['location'] != $city)
            {
                \LotgdNavigation::addHeader('headers.travel.dangerous');
                \LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}&d=1", [
                    'params' => ['key' => $hotkey, 'city' => $city]
                ]);
            }

            if ($session['user']['superuser'] & SU_EDIT_USERS)
            {
                \LotgdNavigation::addHeader('headers.superuser');
                \LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}&su=1", [
                    'params' => ['key' => $hotkey, 'city' => $city]
                ]);
            }

            //-- Restore text domain for navigation
            \LotgdNavigation::setTextDomain();
        break;
        case 'village-text-domain':
            racehuman_checkcity();

            if ($session['user']['location'] == $city)
            {
                $args['textDomain'] = 'racehuman-village-village';
                $args['textDomainNavigation'] = 'racehuman-village-navigation';

                \LotgdNavigation::unBlockLink('stables.php');
            }
        break;
        case 'page-village-tpl-params':
            if ($session['user']['location'] == $city)
            {
                $args['village'] = $city;
                $args['commentarySection'] = "village-{$race}"; //-- Commentary section

                //-- Newest player in realm
                $args['newestplayer'] = (int) get_module_setting("newest-{$city}", 'cities');
                $args['newestname'] = (string) get_module_setting("newest-{$city}-name", 'cities');

                $args['newtext'] = 'newestOther';

                if ($args['newestplayer'] == $session['user']['acctid'])
                {
                    $args['newtext'] = 'newestPlayer';
                    $args['newestname'] = $session['user']['name'];
                }
                elseif (! $args['newestname'] && $args['newestplayer'])
                {
                    $characterRepository = \Doctrine::getRepository('LotgdCore:Characters');
                    $args['newestname'] = $characterRepository->getCharacterNameFromAcctId($args['newestplayer']) ?: 'Unknown';
                    set_module_setting("newest-{$city}-name", $args['newestname'], 'cities');
                }
            }
        break;
        case 'stables-text-domain':
            if ($session['user']['location'] == $city)
            {
                $args['textDomain'] = 'racehuman-stables-stables';
                $args['textDomainNavigation'] = 'racehuman-stables-navigation';

                \LotgdNavigation::unBlockLink('stables.php');
            }
        break;
        case 'stablelocs':
            $args[$city] = \LotgdTranslator::t('locs.stables', ['city' => $city], $race);
        break;
    }

    \LotgdNavigation::setTextDomain();

    return $args;
}

function racehuman_checkcity()
{
    global $session;

    $race = 'racehuman-module';
    $city = get_module_setting('villagename');

    if ($session['user']['race'] == $race && is_module_active('cities') && get_module_pref('homecity', 'cities') != $city)
    { //home city is wrong
        set_module_pref('homecity', $city, 'cities');
    }

    return true;
}

function racehuman_run()
{
}
