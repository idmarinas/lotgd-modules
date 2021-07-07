<?php

// addnews ready
// translator ready
// mail ready
/*
 * Just a silly little village special that is mainly for the "what the heck
 * was that?" response. Generally a gem or gold is found, with a slight
 * chance of something bad or good happening.
 *
 * Version history
 * 0.1 Initial spatter of code
 * 0.2 Functioning code
 * 0.3 Added Bonemarrow Beast, cache discovery, and maximum visits per day
 * 0.4 Added a warning after defeating the Bonemarrow Beast that there might be more
 * 0.5 Added charm loss for defeat if no ff's left.
        Removed the faulty taunt section.
 * 0.6 Corrected several grammar mistakes.
 * 0.7 Corrected an additional grammar mistake.
*/

function spookygold_getmoduleinfo()
{
    return [
        'name'     => 'Spooky Gold',
        'version'  => '2.0.0',
        'author'   => 'Dan Norton, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village Specials',
        'download' => 'core_module',
        'settings' => [
            'Spooky Gold - Settings,title',
            'cowardicechance' => 'Percentage of times running away for something bad to happen,range,0,100,5|10',
            'visitmax'        => 'Number of times allowed to visit the alley per day,int|3',
            'beastchance'     => 'Percentage of times that the beast will attack,range,0,100,5|10',
            'rawchance'       => 'Raw chance of seeing the alley,range,5,50,5|25',
            'cachechance'     => 'Chance of finding a cache of gems or gold,range,0,100,5|10',
        ],
        'prefs' => [
            'Spooky Gold - Preferences,title',
            'visits' => 'How many visits to the alley has the player made today?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function spookygold_seentest()
{
    $visits   = get_module_pref('visits', 'spookygold');
    $visitmax = get_module_setting('visitmax', 'spookygold');

    return get_module_setting('rawchance', 'spookygold') * (($visits) < ($visitmax));
}

function spookygold_install()
{
    module_addeventhook('village', 'require_once("modules/spookygold.php"); return spookygold_seentest();');
    module_addhook('newday');
}

function spookygold_uninstall()
{
    return true;
}

function spookygold_dohook($hookname, $args)
{
    global $session;

    if ('newday' == $hookname)
    {
        set_module_pref('visits', 0);
    }

    return $args;
}

function spookygold_runevent($type)
{
    global $session;

    require_once 'lib/partner.php';

    $from                          = 'village.php?';
    $session['user']['specialinc'] = 'module:spookygold';

    $op = \LotgdRequest::getQuery('op');

    $textDomain = 'module_spookygold';

    $params = [
        'textDomain' => $textDomain,
        'partner'    => get_partner(),
    ];

    \LotgdNavigation::setTextDomain($textDomain);

    switch ($op)
    {
        case 'alley':
            $params['award'] = \mt_rand(0, 1); //-- 0 = gem, 1 = gold

            if ( ! $params['award'])
            {
                \LotgdNavigation::addNav('navigation.nav.alley.gem', 'village.php?op=pickupgem');
            }
            else
            {
                \LotgdNavigation::addNav('navigation.nav.alley.gold', 'village.php?op=pickupgold');
            }

            \LotgdNavigation::addNav('navigation.nav.alley.run', 'village.php?op=dontpickup');
        break;
        case 'pickupgem':
            $params['tpl'] = 'pickupgem';

            $diceroll    = \mt_rand(1, 100);
            $beastchance = (int) get_module_setting('beastchance');
            $cachechance = (int) get_module_setting('cachechance');

            if ($diceroll > $beastchance && $diceroll < (100 - $cachechance))
            {
                $params['cache'] = false;

                \LotgdLog::debug('found a gem in the spooky alley');
                ++$session['user']['gems'];
                $session['user']['specialinc'] = '';
                set_module_pref('visits', get_module_pref('visits') + 1);
            }
            elseif ($diceroll <= $beastchance)
            {
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('flash.message.battle.fight', [], $textDomain));
                spookygold_fight();
            }
            else
            {
                $params['cache'] = true;

                \LotgdLog::debug('found a cache of 5 gems in the spooky alley');
                $session['user']['gems'] += 5;
                $session['user']['specialinc'] = '';
                set_module_pref('visits', get_module_pref('visits') + 1);
            }
        break;
        case 'pickupgold':
            $params['tpl'] = 'pickupgold';
            $diceroll      = \mt_rand(1, 100);
            $cachechance   = get_module_setting('cachechance');

            $params['gold'] = 1;

            if ($diceroll > $cachechance)
            {
                \LotgdLog::debug('found a gold piece in the spooky alley');
                ++$session['user']['gold'];
                $session['user']['specialinc'] = '';
                set_module_pref('visits', get_module_pref('visits') + 1);
            }
            else
            {
                $params['cache'] = true;

                $params['gold'] = $session['user']['level'] * \mt_rand(159, 211);

                \LotgdLog::debug("found a cache of {$params['gold']} in the spooky alley");

                $session['user']['gold'] += $params['gold'];
                $session['user']['specialinc'] = '';

                set_module_pref('visits', get_module_pref('visits') + 1);
            }
        break;
        case 'dontpickup':
            $params['tpl'] = 'dontpickup';

            $session['user']['specialinc'] = '';
            $cowardicechance               = get_module_setting('cowardicechance');
            $wimpychance                   = \mt_rand(1, 100);

            if ($wimpychance <= $cowardicechance)
            {
                $params['coward'] = true;
                $session['user']['charm'] -= 2;
                $session['user']['charm'] = \max($session['user']['charm'], 0);
            }

            set_module_pref('visits', get_module_pref('visits') + 1);
        break;
        case 'walkaway':
            $params['tpl'] = 'walkaway';

            $session['user']['specialinc'] = '';
            set_module_pref('visits', get_module_pref('visits') + 1);
        break;
        case 'fight':
        case 'run':
            spookygold_fight();
        break;
        case '':
        default:
            $params['tpl'] = 'default';
            \LotgdNavigation::addNav('navigation.nav.default.go', 'village.php?op=alley');
            \LotgdNavigation::addNav('navigation.nav.default.ignore', 'village.php?op=walkaway');
        break;
    }

    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/spookygold/run.twig', $params));
}

function spookygold_fight()
{
    global $session;

    $op = \LotgdRequest::getQuery('op');

    $textDomain = 'module_spookygold';

    if ('pickupgem' == $op)
    {
        $badguy = [
            'creaturename'    => \LotgdTranslator::t('badguy.name', [], 'module_spookygold'),
            'creaturelevel'   => $session['user']['level'] + 2,
            'creatureweapon'  => \LotgdTranslator::t('badguy.weapon', [], 'module_spookygold'),
            'creatureattack'  => $session['user']['attack'],
            'creaturedefense' => $session['user']['defense'],
            'creaturehealth'  => \round($session['user']['maxhitpoints'], 0),
            'diddamage'       => 0,
            'type'            => 'bonemarrow',
        ];

        $session['user']['badguy'] = $badguy;
        $op                        = 'fight';
        \LotgdRequest::setQuery('op', 'fight');
    }

    if ('run' == $op)
    {
        \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('flash.message.battle.run', [], 'module_spookygold'));
        $op = 'fight';
        \LotgdRequest::setQuery('op', 'fight');
    }

    if ('fight' == $op)
    {
        $battle = true;
    }

    if ($battle)
    {
        $battleProcessVictoryDefeat = false;

        require_once 'lib/fightnav.php';
        require_once 'public/battle.php';

        if ($victory)
        {
            $battle = false;

            if ($session['user']['hitpoints'] <= 0)
            {
                $lotgdBattleContent['battleend'][] = [
                    'battle.end.victory.hitpoints',
                    [
                        'creatureName' => $badguy['creaturename'],
                    ],
                    $textDomain,
                ];

                $session['user']['hitpoints'] = 1;
            }

            $lotgdBattleContent['battleend'][] = [
                'battle.end.victory.paragraph',
                [
                    'creatureName' => $badguy['creaturename'],
                ],
                $textDomain,
            ];

            \LotgdNavigation::addNav('navigation.nav.battle.pick', 'village.php?op=pickupgem');
            \LotgdNavigation::addNav('navigation.nav.battle.run', 'village.php?op=dontpickup');
        }
        elseif ($defeat)
        {
            $battle = false;

            \LotgdLog::addNews('news.battle.defeated', [
                'playerName' => $session['user']['name'],
            ], $textDomain);

            \LotgdLog::debug('lost to Bonemarrow Beast');

            $session['user']['hitpoints'] = 1;

            $lotgdBattleContent['battleend'][] = [
                'battle.end.defeated.paragraph',
                [
                    'creatureName' => $badguy['creaturename'],
                ],
                $textDomain,
            ];

            if (is_module_active('staminasystem'))
            {
                require_once 'modules/staminasystem/lib/lib.php';

                removestamina(5 * 25000);

                $lotgdBattleContent['battleend'][] = ['battle.end.defeated.stamina', [], $textDomain];
            }
            else
            {
                $session['user']['turns'] -= 5;
                $session['user']['turns']          = \max($session['user']['turns'], 0);
                $lotgdBattleContent['battleend'][] = ['battle.end.defeated.turns', [], $textDomain];
            }

            if ($session['user']['charm'] > 0)
            {
                --$session['user']['charm'];
                $lotgdBattleContent['battleend'][] = ['battle.end.defeated.charm.lost', [], $textDomain];
            }
            else
            {
                $lotgdBattleContent['battleend'][] = ['battle.end.defeated.charm.equal', [], $textDomain];
            }
            $session['user']['specialinc']  = '';
            $session['user']['specialmisc'] = '';
        }
        else
        {
            LotgdNavigation::fightNav(true, true);
        }
    }

    return $battle;
}

function spookygold_run()
{
}
