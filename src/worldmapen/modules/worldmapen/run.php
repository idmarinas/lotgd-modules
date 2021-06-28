<?php
/*
    World Map
    Originally by: Aes
    Updates & Maintenance by: Kevin Hatfield - Arune (khatfield@ecsportal.com)
    Updates & Maintenance by: Roland Lichti - klenkes (klenkes@paladins-inn.de)
    http://www.dragonprime.net
    Updated: Feb 23, 2008
 */

function worldmapen_run_real()
{
    global $session, $badguy, $pvptimeout, $options;

    require_once 'lib/events.php';

    $textDomain    = 'module_worldmapen';
    $displayEvents = false;
    $op            = (string) \LotgdRequest::getQuery('op');

    $battle = false;

    if ('move' == $op && \rawurldecode(\LotgdRequest::getQuery('oloc')) != get_module_pref('worldXYZ'))
    {
        \LotgdResponse::pageDebug(get_module_pref('worldXYZ'));
        $op = 'continue';
        \LotgdRequest::setQuery('op', $op);
    }

    if ('destination' == $op)
    {
        $cname                       = (string) \LotgdRequest::getQuery('cname');
        $session['user']['location'] = $cname;

        \LotgdNavigation::addNav('navigation.nav.enter', 'village.php', [
            'textDomain' => $textDomain,
            'params'     => ['name' => $cname],
        ]);

        \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.destination', ['name' => $cname], $textDomain));
    }

    if ('' != $session['user']['specialinc'] || \LotgdRequest::getQuery('eventhandler'))
    {
        $in_event = handle_event('travel', 'runmodule.php?module=worldmapen&op=continue&', 'Travel');

        if ($in_event)
        {
            \LotgdResponse::pageStart('title.special', [], 'partial_event');
            \LotgdNavigation::addNav('navigation.nav.continue', 'runmodule.php?module=worldmapen&op=continue', ['textDomain' => $textDomain]);
            module_display_events('travel', 'runmodule.php?module=worldmapen&op=continue');
            \LotgdResponse::pageEnd();
        }
    }

    $op               = (string) \LotgdRequest::getQuery('op');
    $subop            = \LotgdRequest::getQuery('subop');
    $act              = \LotgdRequest::getQuery('act');
    $type             = \LotgdRequest::getQuery('type');
    $characterId      = \LotgdRequest::getQuery('character_id');
    $direction        = \LotgdRequest::getQuery('dir');
    $su               = \LotgdRequest::getQuery('su');
    $buymap           = \LotgdRequest::getQuery('buymap');
    $worldmapCostGold = get_module_setting('worldmapCostGold', 'worldmapen');
    $pvp              = \LotgdRequest::getQuery('pvp');

    \LotgdResponse::pageStart('title.journey', [], $textDomain);

    $params = [
        'textDomain' => $textDomain,
    ];

    if ('beginjourney' == $op)
    {
        $params['tpl']          = 'beginjourney';
        $params['mapLoc']       = get_module_pref('worldXYZ', 'worldmapen');
        $params['showSmallMap'] = (bool) get_module_setting('smallmap', 'worldmapen');
        $displayEvents          = true;

        $loc = $session['user']['location'];
        $x   = get_module_setting($loc.'X', 'worldmapen');
        $y   = get_module_setting($loc.'Y', 'worldmapen');
        $z   = get_module_setting($loc.'Z', 'worldmapen');

        set_module_pref('worldXYZ', "{$x},{$y},{$z}");

        $params['mapLinks'] = worldmapen_determinenav();
    }
    elseif ('continue' == $op)
    {
        checkday();

        $params['tpl']          = 'continue';
        $params['mapLinks']     = worldmapen_determinenav();
        $params['mapLoc']       = get_module_pref('worldXYZ', 'worldmapen');
        $params['showSmallMap'] = (bool) get_module_setting('smallmap', 'worldmapen');
    }
    elseif ('tradeturn' == $op)
    {
        checkday();
        $params['tpl']         = 'tradeturn';
        $params['pointsTrade'] = get_module_setting('turntravel', 'worldmapen');

        \LotgdNavigation::addHeader('navigation.category.trade');
        \LotgdNavigation::addNav('navigation.nav.trade.yes', 'runmodule.php?module=worldmapen&op=tradeturnconfirm');
        \LotgdNavigation::addNav('navigation.nav.trade.no', 'runmodule.php?module=worldmapen&op=continue');
    }
    elseif ('tradeturnconfirm' == $op)
    {
        $params['tpl']         = 'tradeturnconfirm';
        $params['pointsTrade'] = get_module_setting('turntravel', 'worldmapen');
        --$session['user']['turns'];

        \LotgdNavigation::addNav('navigation.nav.continue', 'runmodule.php?module=worldmapen&op=continue');

        $ttoday = get_module_pref('traveltoday', 'cities', 'worldmapen');
        set_module_pref('traveltoday', $ttoday - $pointstrade, 'cities', 'worldmapen');
    }
    elseif ('move' == $op)
    {
        checkday();

        $params['tpl'] = 'move';

        if ('World' != $session['user']['location'])
        {
            set_module_pref('lastCity', $session['user']['location']);
            $session['user']['location'] = 'World';
        }

        $session['user']['restorepage'] = 'runmodule.php?module=worldmapen&op=continue';
        $loc                            = get_module_pref('worldXYZ', 'worldmapen');
        list($x, $y, $z)                = \explode(',', $loc);

        if ('north' == $direction)
        {
            ++$y;
        }

        if (get_module_setting('compasspoints') && 'northeast' == $direction)
        {
            ++$y;
            ++$x;
        }

        if (get_module_setting('compasspoints') && 'northwest' == $direction)
        {
            ++$y;
            --$x;
        }

        if ('east' == $direction)
        {
            ++$x;
        }

        if ('south' == $direction)
        {
            --$y;
        }

        if (get_module_setting('compasspoints') && 'southeast' == $direction)
        {
            --$y;
            ++$x;
        }

        if (get_module_setting('compasspoints') && 'southwest' == $direction)
        {
            --$y;
            --$x;
        }

        if ('west' == $direction)
        {
            --$x;
        }

        $terraincost     = worldmapen_terrain_cost($x, $y, $z);
        $encounterbase   = worldmapen_encounter($x, $y, $z);
        $encounterchance = get_module_pref('encounterchance', 'worldmapen');
        $encounter       = ($encounterbase * $encounterchance) / 100;

        $ttoday = get_module_pref('traveltoday', 'cities');
        set_module_pref('traveltoday', $ttoday + $terraincost, 'cities');

        worldmapen_terrain_takestamina($x, $y, $z);
        $xyz = $x.','.$y.','.$z;
        set_module_pref('worldXYZ', $xyz, 'worldmapen');

        \LotgdResponse::pageDebug("Encounter: {$encounterbase} * {$encounterchance} / 100 = {$encounter}");

        //Extra Gubbins pertaining to trading Turns for Travel, added by Caveman Joe
        $useturns       = get_module_setting('useturns');
        $allowzeroturns = get_module_setting('allowzeroturns');
        $playerturns    = $session['user']['turns'];
        $proceed        = 1;
        //the Proceed value is used when the player has hit a monster, to make sure it's okay to actually run the event/monster.
        if (0 == $playerturns && 0 == $allowzeroturns)
        {
            $proceed = 0;
        }

        if (\mt_rand(0, 100) < $encounter && '1' != $su && 1 == $proceed)
        {
            // They've hit a monster!
            if (0 != module_events('travel', get_module_setting('wmspecialchance'), 'runmodule.php?module=worldmapen&op=continue&'))
            {
                if (\LotgdNavigation::checkNavs())
                {
                    \LotgdResponse::pageEnd();
                }

                // Reset the special for good.
                $session['user']['specialinc']  = '';
                $session['user']['specialmisc'] = '';

                $op = '';
                \LotgdRequest::setQuery('op', '');
                \LotgdNavigation::addNav('navigation.nav.continue', 'runmodule.php?module=worldmapen&op=continue');

                module_display_events('travel', 'runmodule.php?module=worldmapen&op=continue');

                \LotgdResponse::pageEnd();
            }

            //-- Check if we're removing a turn when the player encounters a monster, and if so, do it
            if (1 == $useturns)
            {
                --$session['user']['turns'];
            }

            require_once 'lib/forestoutcomes.php';

            $result = lotgd_search_creature(1, $session['user']['level'], $session['user']['level']);

            restore_buff_fields();

            if (0 == \count($result))
            {
                // There is nothing in the database to challenge you,
                // let's give you a doppleganger.
                $badguy = lotgd_generate_doppelganger($session['user']['level']);
            }
            else
            {
                $badguy = buffbadguy($result[0]);
            }

            calculate_buff_fields();
            $badguy['playerstarthp'] = $session['user']['hitpoints'];
            $badguy['diddamage']     = 0;
            $badguy['type']          = 'world';

            $attackstack = [
                'enemies' => [$badguy],
                'options' => [
                    'type' => 'forest',
                ],
            ];

            $session['user']['badguy'] = $attackstack;
            $battle                    = true;
        }
        else
        {
            $params['mapLinks']     = worldmapen_determinenav();
            $params['mapLoc']       = get_module_pref('worldXYZ', 'worldmapen');
            $params['showSmallMap'] = (bool) get_module_setting('smallmap', 'worldmapen');
        }
    }
    elseif ('gypsy' == $op)
    {
        $params['tpl']              = 'gypsy';
        $params['worldmapCostGold'] = $worldmapCostGold;
        $params['buyed']            = null;

        if ('' == $buymap)
        {
            \LotgdNavigation::addNav('navigation.nav.gypsy.buy', 'runmodule.php?module=worldmapen&op=gypsy&buymap=yes', [
                'params' => ['cost' => $worldmapCostGold],
            ]);
            \LotgdNavigation::addNav('navigation.nav.gypsy.forget', 'village.php');
        }
        elseif ('yes' == $buymap)
        {
            if ($session['user']['gold'] < $worldmapCostGold)
            {
                $params['buyed'] = false;

                \LotgdNavigation::addNav('navigation.nav.gypsy.leave', 'village.php');
            }
            else
            {
                $params['buyed'] = true;
                $session['user']['gold'] -= $worldmapCostGold;

                set_module_pref('worldmapbuy', 1);

                \LotgdNavigation::villageNav();
            }
        }
    }
    elseif ('viewmap' == $op)
    {
        $params['tpl']      = 'viewmap';
        $params['mapLinks'] = worldmapen_determinenav();
    }
    elseif ('camp' == $op)
    {
        if ($session['user']['loggedin'])
        {
            $session['user']['loggedin']    = false;
            $session['user']['restorepage'] = 'runmodule.php?module=worldmapen&op=wake';

            modulehook('player-logout');
            \LotgdTool::saveUser();

            \LotgdSession::invalidate();

            \LotgdKernel::get('cache.app')->delete('charlisthomepage');
            \LotgdKernel::get('cache.app')->delete('list.php-warsonline');

            \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('logout.success', [], 'page-login'));
        }

        $session = [];

        return redirect('index.php', 'Redirected to Index from World Map');
    }
    elseif ('wake' == $op)
    {
        // runmodule.php calls do_forced_nav,
        $session['user']['alive'] = ($session['user']['hitpoints'] > 0);

        checkday();

        $params['tpl']          = 'wake';
        $params['mapLoc']       = get_module_pref('worldXYZ', 'worldmapen');
        $params['showSmallMap'] = (bool) get_module_setting('smallmap', 'worldmapen');
        $params['mapLinks']     = worldmapen_determinenav();
    }
    elseif ('combat' == $op)
    {
        // Okay, we've picked a person to fight.
        require_once 'lib/pvpsupport.php';
        $badguy = setup_pvp_target($characterId);

        if (\is_string($badguy))
        {
            $battle = false;
            \LotgdFlashMessages::addErrorMessage(\LotgdTranslate::t($badguy, [], 'page-pvp'));

            $params['mapLoc']       = get_module_pref('worldXYZ', 'worldmapen');
            $params['showSmallMap'] = (bool) get_module_setting('smallmap', 'worldmapen');
            $params['mapLinks']     = worldmapen_determinenav();
        }
        elseif (\is_array($badguy))
        {
            $battle                    = true;
            $badguy['type']            = 'pvp';
            $options['type']           = 'pvp';
            $attackstack['enemies'][0] = $badguy;
            $attackstack['options']    = $options;
            $session['user']['badguy'] = $attackstack;
            --$session['user']['playerfights'];
        }
    }
    elseif ('fight' == $op || 'run' == $op)
    {
        $params['tpl'] = 'fight';

        if ( ! \LotgdRequest::getQuery('frombio'))
        {
            $battle = true;
        }
        else
        {
            $params['mapLinks']     = worldmapen_determinenav();
            $params['mapLoc']       = get_module_pref('worldXYZ', 'worldmapen');
            $params['showSmallMap'] = (bool) get_module_setting('smallmap', 'worldmapen');
        }

        $free = 100;

        if ('run' == $op && ! $pvp)
        {
            if (\mt_rand(1, 5) < 3 && $free)
            {
                $battle = false;
                $ttoday = get_module_pref('traveltoday', 'cities');
                set_module_pref('traveltoday', $ttoday + 1, 'cities');

                $params['mapLinks']     = worldmapen_determinenav();
                $params['mapLoc']       = get_module_pref('worldXYZ', 'worldmapen');
                $params['showSmallMap'] = (bool) get_module_setting('smallmap', 'worldmapen');
            }
            else
            {
                $battle = true;
                \LotgdFlashMessages::addErrorMessage('flash.message.figth.run.pve', [], $textDomain);

                $op = 'fight';
                \LotgdRequest::setQuery('op', $op);
            }
        }
        elseif ('run' == $op && $pvp)
        {
            $battle = true;
            \LotgdFlashMessages::addErrorMessage('flash.message.figth.run.pvp', [], $textDomain);
            $op = 'fight';
            \LotgdRequest::setQuery('op', $op);
        }
    }

    if ($battle)
    {
        $params['tpl'] = 'fight';

        //-- Any data for personalize results
        $battleShowResult = false; //-- Show result of battle. If no need any extra modification of result no need change this

        if ($pvp)
        {
            $battleProcessVictoryDefeat = false; //-- Process victory or defeat functions when the battle is over
        }

        require_once 'public/battle.php';

        if ($victory)
        {
            $battle = false;

            if ($pvp)
            {
                require_once 'lib/pvpsupport.php';
                $aliveloc = $badguy['location'];
                pvpvictory($badguy, $aliveloc, $options);
                \LotgdLog::addNews('news.battle.victory', [
                    'playerName'   => $session['user']['name'],
                    'creatureName' => $badguy['creaturename'],
                ], $textDomain);
            }

            $params['mapLoc']       = get_module_pref('worldXYZ', 'worldmapen');
            $params['showSmallMap'] = (bool) get_module_setting('smallmap', 'worldmapen');
            $params['mapLinks']     = worldmapen_determinenav();
        }
        elseif ($defeat)
        {
            $battle = false;
            // Reset the players body to the last city they were in
            $session['user']['location'] = get_module_pref('lastCity');

            if ($pvp)
            {
                require_once 'lib/pvpsupport.php';
                require_once 'lib/taunt.php';

                pvpdefeat($badguy, $badguy['location']);

                \LotgdLog::addNews('deathmessage', [
                    'deathmessage' => [
                        'deathmessage' => 'news.pvp.defeated',
                        'params'       => [
                            'playerName'   => $session['user']['name'],
                            'creatureName' => $badguy['creaturename'],
                            'location'     => $badguy['location'],
                        ],
                        'textDomain' => $textDomain,
                    ],
                    'taunt' => \LotgdTool::selectTaunt(),
                ], '');
            }

            \LotgdFlashMessages::addErrorMessage('flash.message.battle.defeated', ['location' => $session['user']['location']], $textDomain);
        }
        else
        {
            $battle = true;

            require_once 'lib/fightnav.php';
            $allow = true;
            $extra = '';

            if ($pvp)
            {
                $allow = false;
                $extra = 'pvp=1&';
            }
            fightnav($allow, $allow, "runmodule.php?module=worldmapen&{$extra}");
        }

        battleshowresults($lotgdBattleContent);
    }

    $params['battle']   = $battle;
    $params['location'] = $session['user']['location'];

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/worldmapen/run.twig', $params));

    if ($displayEvents)
    {
        module_display_events('travel', 'runmodule.php?module=worldmapen&op=continue');
    }

    \LotgdResponse::pageEnd();
}
