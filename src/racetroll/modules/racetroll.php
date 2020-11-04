<?php

// translator ready
// addnews ready
// mail ready

function racetroll_getmoduleinfo()
{
    return [
        'name' => 'Race - Troll',
        'version' => '2.0.0',
        'author' => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Races',
        'download' => 'core_module',
        'settings' => [
            'Trollish Race Settings,title',
            'villagename' => 'Name for the troll village|Glukmoore',
            'minedeathchance' => 'Chance for Trolls to die in the mine,range,0,100,1|90',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function racetroll_install()
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

            ->setParameter('old', 'Troll')
            ->setParameter('new', 'racetroll-module')

            ->getQuery()
            ->execute()
        ;

        //-- Section of commentary
        $query = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Commentary', 'u')
            ->set('u.section', ':new')
            ->where('u.section = :old')

            ->setParameter('old', 'village-Troll')
            ->setParameter('new', 'village-racetroll-module')

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
    module_addhook('travel');
    module_addhook('validlocation');
    module_addhook('validforestloc');
    module_addhook('moderate-comment-sections');
    module_addhook('changesetting');
    module_addhook('raceminedeath');
    module_addhook('pvpadjust');
    module_addhook('adjuststats');
    module_addhook('racenames');

    return true;
}

function racetroll_uninstall()
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

            ->setParameter('race', 'racetroll-module')

            ->getQuery()
            ->execute()
        ;

        if ('racetroll-module' == $session['user']['race'])
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

function racetroll_dohook($hookname, $args)
{
    //yeah, the $resline thing is a hack.  Sorry, not sure of a better way
    // to handle this.
    // Pass it in via args?
    global $session, $resline;

    $city = get_module_setting('villagename');
    $race = 'racetroll-module';

    \LotgdNavigation::setTextDomain($race);

    switch ($hookname)
    {
        case 'racenames':
            $args[$race] = \LotgdTranslator::t('character.racename', [], $race);
        break;
        case 'pvpadjust':
            if ($args['race'] == $race)
            {
                $args['creatureattack'] += (1 + floor($args['creaturelevel'] / 5));
            }
        break;
        case 'adjuststats':
            if ($args['race'] == $race)
            {
                $args['attack'] += (1 + floor($args['level'] / 5));
            }
        break;
        case 'raceminedeath':
            if ($session['user']['race'] == $race)
            {
                $args['chance'] = get_module_setting('minedeathchance');
            }
        break;
        case 'changesetting':
            // Ignore anything other than villagename setting changes
            if ('villagename' == $args['setting'] && 'racetroll' == $args['module'])
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

            \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('racetroll/dohook/chooserace.twig', $params));
        break;
        case 'setrace':
            if ($session['user']['race'] == $race)
            {
                \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('racetroll/dohook/setrace.twig', []));

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
        case 'newday':
            if ($session['user']['race'] == $race)
            {
                racetroll_checkcity();
                apply_buff('racialbenefit', [
                    'name' => \LotgdTranslator::t('racial.buff.name', [], $race),
                    'atkmod' => '(<attack>?(1+((1+floor(<level>/5))/<attack>)):1)',
                    'allowinpvp' => 1,
                    'allowintrain' => 1,
                    'rounds' => -1,
                    'schema' => 'raceelf-module',
                ]);
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
            racetroll_checkcity();

            if ($session['user']['location'] == $city)
            {
                $args['textDomain'] = 'racetroll-village-village';
                $args['textDomainNavigation'] = 'racetroll-village-navigation';
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
    }

    \LotgdNavigation::setTextDomain();

    return $args;
}

function racetroll_checkcity()
{
    global $session;

    $race = 'racetroll-module';
    $city = get_module_setting('villagename');

    if ($session['user']['race'] == $race && is_module_active('cities') && get_module_pref('homecity', 'cities') != $city)
    { //home city is wrong
        set_module_pref('homecity', $city, 'cities');
    }

    return true;
}

function racetroll_run()
{
}
