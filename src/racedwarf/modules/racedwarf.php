<?php

function racedwarf_getmoduleinfo()
{
    return [
        'name' => 'Race - Dwarf',
        'version' => '2.0.0',
        'author' => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Races',
        'download' => 'core_module',
        'settings' => [
            'Dwarven Race Settings,title',
            'villagename' => 'Name for the dwarven village|Qexelcrag',
            'minedeathchance' => 'Chance for Dwarves to die in the mine,range,0,100,1|5',
        ],
        'prefs-drinks' => [
            'Dwarven Race Drink Preferences,title',
            'servedkeg' => 'Is this drink served in the dwarven inn?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function racedwarf_install()
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

            ->setParameter('old', 'Dwarf')
            ->setParameter('new', 'racedwarf-module')

            ->getQuery()
            ->execute()
        ;

        //-- Section of commentary
        $query = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Commentary', 'u')
            ->set('u.section', ':new')
            ->where('u.section = :old')

            ->setParameter('old', 'village-Dwarf')
            ->setParameter('new', 'village-racedwarf-module')

            ->getQuery()
            ->execute()
        ;

        $companionRepositoy = \Doctrine::getRepository('LotgdCore:Companions');
        $entity = $companionRepositoy->findOneBy(['name' => 'Grizzly Bear']);

        if (! $entity)
        { //-- Only if not find
            $entity = $companionRepositoy->hydrateEntity([
                'name' => 'Grizzly Bear',
                'category' => 'Wild Beasts',
                'description' => 'You look at the beast knowing that this Grizzly Bear will provide an effective block against attack with its long curved claws and massive body of silver-tipped fur.',
                'attack' => 1,
                'attackperlevel' => 2,
                'defense' => 5,
                'defenseperlevel' => 2,
                'maxhitpoints' => 25,
                'maxhitpointsperlevel' => 25,
                'abilities' => ['fight' => 0, 'heal' => 0, 'magic' => 0, 'defend' => 1],
                'cannotdie' => 0,
                'cannotbehealed' => 0,
                'companionlocation' => get_module_setting('villagename', 'racedwarf'),
                'companionactive' => 1,
                'companioncostdks' => 0,
                'companioncostgems' => 4,
                'companioncostgold' => 600,
                'jointext' => 'You hear a low, deep belly growl coming from a shadowed corner of the Bestiarium.  Curious you walk over to investigate your purchase. As you approach a large form shuffles on all four legs towards the front of its hewn rock enclosure.`n`nThe hunched shoulders of the largest bear you have ever seen ripple as its front haunches push against the ground causing it to stand on its hind legs.  It makes another low growl before dropping back on all four legs to follow you on your adventure.',
                'dyingtext' => 'The grizzly gets scared by the multitude of blows and hits he has to take and flees into the forest.',
                'allowinshades' => 1,
                'allowinpvp' => 0,
                'allowintrain' => 0,
            ]);

            \Doctrine::persist($entity);
            \Doctrine::flush();

            debug('Inserted new companion: Grizzly Bear');
        }
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);

        return false;
    }

    module_addhook('chooserace');
    module_addhook('setrace');
    module_addhook('creatureencounter');
    module_addhook('village-text-domain');
    module_addhook('page-village-tpl-params');
    module_addhook('travel');
    module_addhook('village');
    module_addhook('validlocation');
    module_addhook('validforestloc');
    module_addhook('moderate-comment-sections');
    module_addhook('drinks-text');
    module_addhook('changesetting');
    module_addhook('drinks-check');
    module_addhook('raceminedeath');
    module_addhook('racenames');
    module_addhook('camplocs');
    module_addhook('mercenarycamptext');

    return true;
}

function racedwarf_uninstall()
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

        $query = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Companions', 'u')
            ->set('u.companionlocation', 'all')
            ->where('u.companionlocation = :loc')

            ->setParameter('loc', $vname)

            ->getQuery()
            ->execute()
        ;
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);

        return false;
    }

    return true;
}

function racedwarf_dohook($hookname, $args)
{
    //yeah, the $resline thing is a hack.  Sorry, not sure of a better way
    //to handle this.
    // It could be passed as a hook arg?
    global $session, $resline;

    $city = get_module_setting('villagename');
    $race = 'racedwarf-module';

    switch ($hookname)
    {
        case 'racenames':
            $args[$race] = \LotgdTranslator::t('character.racename', [], $race);
        break;
        case 'raceminedeath':
            if ($session['user']['race'] == $race)
            {
                $args['chance'] = get_module_setting('minedeathchance');
                $args['racesave'] = \LotgdTranslator::t('raceminedeath.save', [], 'racedwarf-module');
                $args['schema'] = 'racedwarf-module';
            }
        break;
        case 'changesetting':
            // Ignore anything other than villagename setting changes for myself
            if ('villagename' == $args['setting'] && 'racedwarf' == $args['module'])
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
            \LotgdNavigation::addNav('navigation.nav.character.racename', "newday.php?setrace={$race}{$resline}");

            $params = [
                'city' => $city,
                'race' => $race,
                'resLine' => $resline
            ];

            rawoutput(\LotgdTheme::renderModuleTemplate('racedwarf/dohook/chooserace.twig', $params));
        break;
        case 'setrace':
            if ($session['user']['race'] == $race)
            {
                rawoutput(\LotgdTheme::renderModuleTemplate('racedwarf/dohook/setrace.twig', []));

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
                $args["village-$race"] = \LotgdTranslator::t('locs.moderate', [ 'city' => $city ], $race);
            }
        break;
        case 'creatureencounter':
            if ($session['user']['race'] == $race)
            {
                //get those folks who haven't manually chosen a race
                racedwarf_checkcity();
                $args['creaturegold'] = round($args['creaturegold'] * 1.2, 0);
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
                    'params' => [ 'key' => $hotkey, 'city' => $city]
                ]);
            }
            elseif ($session['user']['location'] != $city)
            {
                \LotgdNavigation::addHeader('headers.travel.dangerous');
                \LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}&d=1", [
                    'params' => [ 'key' => $hotkey, 'city' => $city]
                ]);
            }

            if ($session['user']['superuser'] & SU_EDIT_USERS)
            {
                \LotgdNavigation::addHeader('headers.superuser');
                \LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}&su=1", [
                    'params' => [ 'key' => $hotkey, 'city' => $city]
                ]);
            }

            //-- Restore text domain for navigation
            \LotgdNavigation::setTextDomain();
        break;
        case 'village-text-domain':
            racedwarf_checkcity();

            if ($session['user']['location'] == $city)
            {
                $args['textDomain'] = 'racedwarf-village-village';
                $args['textDomainNavigation'] = 'racedwarf-village-navigation';

                \LotgdNavigation::unBlockLink('mercenarycamp.php');
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

                \LotgdNavigation::addHeader('headers.tavern');
                \LotgdNavigation::addNav('navs.inndwarf', 'runmodule.php?module=racedwarf&op=ale');
            }
        break;
        case 'drinks-text':
            if ($session['user']['location'] != $city)
            {
                break;
            }

            $args['textDomain'] = 'racedwarf-module';
        break;
        case 'drinks-check':
            if ($session['user']['location'] == $city)
            {
                foreach($args as $key => $drink)
                {
                    $val = get_module_objpref('drinks', $drink['id'], 'servedkeg');
                    $args[$key]['allowdrink'] = $val;
                }
            }
            break;
        case 'camplocs':
            $args[$city] = [
                'location.village.of',
                ['name' => $city],
                'app-default'
            ];
        break;
        case 'mercenarycamptext':
            if ($session['user']['location'] == $city)
            {
                $args['title'] = 'A Bestiarium';
                $args['schemas']['title'] = 'module-racedwarf';

                $args['desc'] = [
                    '`5You are making your way to the Bestiarium deep in the bowels of the dwarven mountain stronghold.',
                    'The sounds of a massive struggle echo off the hewn rock walls of the cavernous passageway.',
                    'Scuffling is punctuated with the sounds of snarling and the impact of a heavy body slamming into another.`n`n',

                    'As you round the corner you find yourself at the edge of an arena.',
                    'Around the walls are carved out stalls which contain beasts of various shapes, sizes and abilities.`n`n',

                    'In the arena, a `&white wolf `5whose size equals that of a mountain pony is lunging towards a massive `~black bear`5.',
                    '`~The bear`5 on his hind legs stands as tall as an oak.',
                    'It raises a paw as `&the wolf `5leaps towards him, then with a movement so quick you nearly miss it, `&the wolf `5is batted away to fall on its side.',
                    'Apparently enraged, `&the wolf`5 leaps snarling to its feet to prepare to lunge again.`n`n',

                    'At that moment a stocky dwarf standing at the edge of the arena raises his finger and thumb to his mouth.',
                    'A piercing whistle cuts through the air.',
                    '`~The black bear `5lowers himself to all fours and shakes his body, then yawns.',
                    '`&The white wolf `5pauses, then lays down with his tongue hanging in a pant.',
                    'Its yellow eyes never leaving you as you walk towards the dwarf.`n`n',

                    '"`tGreetings, Dwalin!`5" you call out as you approach.',
                    '"`tI am in need of a beast to accompany me on my adventures.',
                    'What do you have available this day?`5"`n`n'
                ];
                $args['schemas']['desc'] = 'module-racedwarf';

                $args['buynav'] = 'Buy a Beast';
                $args['schemas']['buynav'] = 'module-racedwarf';

                $args['healnav'] = '';
                $args['schemas']['healnav'] = '';

                $args['healtext'] = '';
                $args['schemas']['healtext'] = '';

                $args['healnotenough'] = '';
                $args['schemas']['healnotenough'] = '';

                $args['healpaid'] = '';
                $args['schemas']['healpaid'] = '';

                // We don not want the healer in this camp.
                blocknav('mercenarycamp.php?op=heal', true);
            }
        break;
    }

    return $args;
}

function racedwarf_checkcity()
{
    global $session;

    $race = 'racedwarf-module';

    $city = get_module_setting('villagename');

    if ($session['user']['race'] == $race && is_module_active('cities') && get_module_pref('homecity', 'cities') != $city)
    { //home city is wrong
        set_module_pref('homecity', $city, 'cities');
    }

    return true;
}

function racedwarf_run()
{
    $op = httpget('op');

    switch ($op)
    {
        case 'ale':
            require_once 'lib/villagenav.php';
            page_header('Great Kegs of Ale');
            output('`3You make your way over to the great kegs of ale lined up near by, looking to score a hearty draught from their mighty reserves.');
            output('A mighty dwarven barkeep named `$G`4argoyle`3 stands at least 4 feet tall, and is serving out the drinks to the boisterous crowd.');
            addnav('Drinks');
            modulehook('ale');
            addnav('Other');
            villagenav();
            page_footer();
        break;
    }
}
