<?php

// translator ready
// addnews ready
// mail ready

function newbieisland_getmoduleinfo()
{
    return [
        'name'     => 'Newbie Island',
        'version'  => '2.0.0',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'General',
        'download' => 'core_module',
        'settings' => [
            'Newbie Island,title',
            'villagename' => 'Name for the newbie island|Isle of Wen',
        ],
        'prefs' => [
            'Newbie Island,title',
            'leftisland' => 'Left the newbie island,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
        ],
    ];
}

function newbieisland_install()
{
    module_addhook('newday');
    module_addhook('village-text-domain');
    module_addhook('stabletext');
    module_addhook('charstats');
    module_addhook('validlocation');
    module_addhook('moderate-comment-sections');
    module_addhook('changesetting');
    module_addhook('forest-desc');
    module_addhook('travel');
    module_addhook('page-village-tpl-params');
    module_addhook('battle-defeat-end');
    module_addhook('pvpcount');
    module_addhook_priority('everyhit-loggedin', 25);
    module_addhook('scrylocation');

    return true;
}

function newbieisland_uninstall()
{
    global $session;

    $vname = getsetting('villagename', LOCATION_FIELDS);
    $gname = get_module_setting('villagename');

    $repository = \Doctrine::getRepository('LotgdCore:Characters');
    $query      = $repository->getQueryBuilder();

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

    return true;
}

function newbieisland_dohook($hookname, $args)
{
    //yeah, the $resline thing is a hack.  Sorry, not sure of a better way
    // to handle this.
    // Pass it as an arg?
    global $session, $resline;

    $textDomain = 'newbieisland-module';
    $city       = get_module_setting('villagename', 'newbieisland');

    newbieisland_checkcity();

    switch ($hookname)
    {
        case 'everyhit-loggedin':
            global $SCRIPT_NAME;

            // We need to do this so that we can do the location test
            // correctly and exclude non-basic races, and all the gewgaws on
            // the newday page(s).
            if ('newday.php' == $SCRIPT_NAME)
            {
                newbieisland_checkcity();
            }

            // Exit early if the user isn't in the newbie island.
            // Do not block anything in the grotto!
            if ($session['user']['location'] != $city || 'superuser.php' == $SCRIPT_NAME)
            {
                break;
            }

            // actually since we're doing this sorta globally, let's just
            // do it globally.
            // Block all modules by default
            blockmodule(true);
            // Make sure to unblock ourselves
            unblockmodule('newbieisland');
            // You need to explicitly allow newbies to interact with a module
            // in the village or forest
            unblockmodule('staminasystem');
            unblockmodule('staminacorecombat');
            unblockmodule('displaycp');
            unblockmodule('mysticalshop');
            unblockmodule('mysticalshop_buffs');
            unblockmodule('inventory');
            unblockmodule('inventorypopup');
            unblockmodule('inv_statvalues');
            unblockmodule('tutor');
            unblockmodule('raceelf');
            unblockmodule('racehuman');
            unblockmodule('racedwarf');
            unblockmodule('racetroll');
            unblockmodule('specialtydarkarts');
            unblockmodule('specialtythiefskills');
            unblockmodule('specialtymysticpower');
            unblockmodule('specialtychickenmage');
            // Even newbies get advertising
            unblockmodule('advertising');
            unblockmodule('advertising_google');
            unblockmodule('advertising_amazon');
            unblockmodule('advertising_splitreason');
            unblockmodule('funddrive');
            unblockmodule('funddriverewards');
            unblockmodule('customeq');
            unblockmodule('expbar');
            unblockmodule('healthbar');
            unblockmodule('serversuspend');
            unblockmodule('timeplayed');
            unblockmodule('collapse');
            unblockmodule('mutemod');
            unblockmodule('faqmute');
            unblockmodule('extlinks');
            unblockmodule('pvpimmunity');
            unblockmodule('deputymoderator');
            unblockmodule('unclean');
            unblockmodule('stattracker');
            unblockmodule('topwebgames');
            unblockmodule('newdaybar');
            unblockmodule('unblockmodules');

            //-- Hook used for module unblockmodules
            modulehook('newbieisland-everyhit-loggedin');

            //Let newbies see the Travel FAQ
            //Nobody ever looks at the FAQ more than once
            //so newbies have to see it right at the start
            if ('petition.php' == $SCRIPT_NAME)
            {
                unblockmodule('cities');
            }
        break;
        case 'pvpcount':
            if ($args['loc'] == $city)
            {
                $args['handled'] = 1;
            }
        break;
        case 'battle-defeat-end':
            if ($session['user']['location'] != $city)
            {
                break;
            }

            static $runonce = false;

            if (false !== $runonce)
            {
                break;
            }

            global $options, $lotgdBattleContent;

            $runonce = true;

            if ('forest' == $options['type'])
            {
                $lotgdBattleContent['battleend'][] = [
                    'battle.defeated',
                    ['creatureName' => $args['creaturename']],
                    $textDomain,
                ];

                battleshowresults($lotgdBattleContent);

                \LotgdNavigation::addNav('common.nav.continue', 'runmodule.php?module=newbieisland&op=resurrect', [
                    'textDomain' => 'navigation-app',
                ]);

                \LotgdResponse::pageEnd();
            }
        break;
        case 'changesetting':
            // Ignore anything other than villagename setting changes
            if ('villagename' == $args['setting'] && 'newbieisland' == $args['module'])
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
                    $query                     = $moduleUserPrefsRepository->getQueryBuilder();
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
        case 'newday':
            newbieisland_checkcity();

            global $session;

            if ($session['user']['location'] == $city)
            {
                $turns = getsetting('turns', 10);
                $turns = \round($turns / 2);

                if (is_module_active('staminasystem'))
                {
                    require_once 'modules/staminasystem/lib/lib.php';

                    $stamina = $turns * 25000;
                    $args['turnstoday'] .= ", Newbie Island: Stamina {$stamina}";

                    addstamina($stamina);
                }
                else
                {
                    $args['turnstoday'] .= ", Newbie Island: {$turns}";
                    $session['user']['turns'] += $turns;
                }

                apply_buff('newbiecoddle', [
                    'name'             => '',
                    'rounds'           => -1,
                    'minioncount'      => 1,
                    'mingoodguydamage' => 0,
                    'maxgoodguydamage' => '(<hitpoints><<maxhitpoints>?-1:0)',
                    'effectfailmsg'    => LotgdTranslator::t('buff.effectfailmsg', [], $textDomain),
                    'effectnodmgmsg'   => '',
                    'schema'           => 'newbiecoddle',
                ]);

                $args['includeTemplatesPost']['module/newbieisland/dohook/newday.twig'] = [
                    'textDomain'    => $textDomain,
                    'staminaSystem' => is_module_active('staminasystem'),
                    'turns'         => $turns,
                ];
            }
        break;
        case 'validlocation':
            if (is_module_active('cities'))
            {
                $args[$city] = 'village-newbie';
            }
        break;
        case 'moderate-comment-sections':
            $args['village-newbie'] = $city;
        break;
        case 'travel':
            $hotkey = \substr($city, 0, 1);
            $ccity  = \urlencode($city);

            //-- Change text domain for navigation
            \LotgdNavigation::setTextDomain('cities-navigation');

            if ($session['user']['superuser'] & SU_EDIT_USERS)
            {
                \LotgdNavigation::addHeader('headers.superuser');
                \LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}&su=1", [
                    'params' => ['key' => $hotkey, 'city' => $city],
                ]);
            }

            //-- Restore text domain for navigation
            \LotgdNavigation::setTextDomain();
        break;
        case 'forest-desc':
            if ($session['user']['location'] == $city)
            {
                \LotgdNavigation::blockHideLink('forest.php?op=search&type=suicide');
                \LotgdNavigation::blockHideLink('forest.php?op=search&type=thrill');
                \LotgdNavigation::blockHideLink('runmodule.php?module=outhouse');

                if ($session['user']['level'] >= 5)
                {
                    $args[] = [
                        'section.forest',
                        [],
                        $textDomain,
                    ];

                    \LotgdNavigation::blockHideLink('forest.php?op=search', true);
                }
            }
        break;
        case 'village-text-domain':
            newbieisland_checkcity();

            if ($session['user']['location'] == $city)
            {
                $args['textDomain']           = 'newbieisland-village-village';
                $args['textDomainNavigation'] = 'newbieisland-village-navigation';

                \LotgdNavigation::blockHideLink('pvp.php');
                \LotgdNavigation::blockHideLink('lodge.php');
                \LotgdNavigation::blockHideLink('gypsy.php');
                \LotgdNavigation::blockHideLink('pavilion.php');
                \LotgdNavigation::blockHideLink('inn.php');
                \LotgdNavigation::blockHideLink('stables.php');
                \LotgdNavigation::blockHideLink('gardens.php');
                \LotgdNavigation::blockHideLink('rock.php');
                \LotgdNavigation::blockHideLink('clan.php');
                \LotgdNavigation::blockHideLink('mercenarycamp.php');
                \LotgdNavigation::blockHideLink('hof.php');
                // Make sure that Blusprings can show up on newbie island.
                \LotgdNavigation::unBlockLink('train.php');
                //if you want your module to appear in the newbie village, you'll have to hook on village
                //and unblocknav() it.  I warn you, very very few modules will ever be allowed in the newbie
                //village and get support for appearing in the core distribution; one of the major reasons
                //FOR the newbie village is to keep the village very simple for new players.
            }
        break;
        case 'page-village-tpl-params':
            if ($session['user']['location'] == $city)
            {
                $args['village']           = $city;
                $args['commentarySection'] = 'village-newbie'; //-- Commentary section

                //-- Newest player in realm
                $args['newestplayer'] = (int) get_module_setting("newest-{$city}", 'cities');
                $args['newestname']   = (string) get_module_setting("newest-{$city}-name", 'cities');

                $args['newtext'] = 'newestOther';

                if ($args['newestplayer'] == $session['user']['acctid'])
                {
                    $args['newtext']    = 'newestPlayer';
                    $args['newestname'] = $session['user']['name'];
                }
                elseif ( ! $args['newestname'] && $args['newestplayer'])
                {
                    $characterRepository = \Doctrine::getRepository('LotgdCore:Characters');
                    $args['newestname']  = $characterRepository->getCharacterNameFromAcctId($args['newestplayer']) ?: 'Unknown';
                    set_module_setting("newest-{$city}-name", $args['newestname'], 'cities');
                }

                //-- Only can leave de island when player is level 2 or above
                if ($session['user']['level'] > 1)
                {
                    \LotgdNavigation::setTextDomain('newbieisland-village-navigation');

                    \LotgdNavigation::addHeader('headers.gate');
                    \LotgdNavigation::addNav('navs.leave', 'runmodule.php?module=newbieisland&op=leave', [
                        'params' => ['city' => $city],
                    ]);

                    \LotgdNavigation::setTextDomain();
                    \LotgdNavigation::unBlockLink('runmodule.php?module=newbieisland&op=leave');
                }
            }
        break;
    }

    return $args;
}

function newbieisland_checkcity()
{
    global $session;

    $city = get_module_setting('villagename');

    if ( ! get_module_pref('leftisland') && 0 == $session['user']['dragonkills'] && $session['user']['level'] <= 5)
    {
        $session['user']['location'] = $city;
    }
}

function newbieisland_run()
{
    global $session;

    $city = get_module_setting('villagename');
    $op   = \LotgdRequest::getQuery('op');

    $textDomain = 'newbieisland-module';

    \LotgdNavigation::setTextDomain('newbieisland-village-navigation');

    switch ($op)
    {
        case 'leave':
            \LotgdNavigation::addHeader('headers.stay');
            \LotgdNavigation::villageNav();

            \LotgdResponse::pageStart('section.leave.title', [], $textDomain);

            $params = [
                'textDomain' => $textDomain,
                'canLeave'   => false,
                'city'       => $city,
            ];

            if ($session['user']['dragonkills'] >= 0 || $session['user']['level'] > 4)
            {
                $params['canLeave'] = true;
                \LotgdNavigation::addHeader('headers.leave');
                \LotgdNavigation::addNav('navs.raft', 'runmodule.php?module=newbieisland&op=raft');
            }

            \LotgdResponse::pageAddContent(LotgdTheme::renderModuleTemplate('newbieisland/run/leave.twig', $params));

            \LotgdResponse::pageEnd();
        break;
        case 'raft':
            \LotgdResponse::pageStart('section.raft.title', [], $textDomain);

            set_module_pref('leftisland', true);

            \LotgdNavigation::addNav('navs.village', 'village.php');

            if (is_module_active('cities'))
            {
                //new farmthing, set them to wandering around this city.
                set_module_setting("newest-{$city}", $session['user']['acctid'], 'cities');
                $session['user']['location'] = get_module_pref('homecity', 'cities');
            }
            else
            {
                $session['user']['location'] = getsetting('villagename', LOCATION_FIELDS);
            }

            $params = [
                'textDomain' => $textDomain,
                'city'       => $city,
            ];

            \LotgdResponse::pageAddContent(LotgdTheme::renderModuleTemplate('newbieisland/run/raft.twig', $params));

            \LotgdResponse::pageEnd();
        break;
        case 'resurrect':
            \LotgdResponse::pageStart('section.resurrect.title', [], $textDomain);

            $params = [
                'textDomain'    => $textDomain,
                'city'          => $city,
                'deathOverlord' => getsetting('deathoverlord', '`$Ramius`0'),
            ];

            $session['user']['hitpoints'] = 1;
            $session['user']['alive']     = true;

            \LotgdNavigation::villageNav();

            \LotgdResponse::pageAddContent(LotgdTheme::renderModuleTemplate('newbieisland/run/resurrect.twig', $params));

            \LotgdResponse::pageEnd();
        break;
    }

    \LotgdNavigation::setTextDomain();
}
