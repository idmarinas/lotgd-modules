<?php

require_once 'modules/staminasystem/lib/lib.php';

/*STAMINA ACTIONS USED

Hunting - Normal
Used when hunting for a normal-level creature to kill.

Hunting - Big Trouble
Used when thrillseeking.

Hunting - Easy Fights
Used when slumming.

Hunting - Suicidal
Used when searching suicidally.

*/

function staminacorecombat_getmoduleinfo()
{
    return [
        'name' => 'Stamina System - Core Combat',
        'version' => '2.0.0',
        'author' => 'Dan Hall, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Stamina',
        'download' => '',
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}
function staminacorecombat_install()
{
    module_addhook_priority('forest', 0);
    module_addhook('startofround-prebuffs');
    module_addhook('endofround');
    module_addhook('fightnav');
    module_addhook('fightnav-graveyard');

    install_action('Hunting - Normal', [
        'maxcost' => 25000,
        'mincost' => 10000,
        'firstlvlexp' => 1000,
        'expincrement' => 1.08,
        'costreduction' => 150,
        'class' => 'Hunting'
    ]);
    install_action('Hunting - Big Trouble', [
        'maxcost' => 30000,
        'mincost' => 10000,
        'firstlvlexp' => 1000,
        'expincrement' => 1.08,
        'costreduction' => 200,
        'class' => 'Hunting'
    ]);
    install_action('Hunting - Easy Fights', [
        'maxcost' => 20000,
        'mincost' => 10000,
        'firstlvlexp' => 1000,
        'expincrement' => 1.08,
        'costreduction' => 100,
        'class' => 'Hunting'
    ]);
    install_action('Hunting - Suicidal', [
        'maxcost' => 35000,
        'mincost' => 10000,
        'firstlvlexp' => 1000,
        'expincrement' => 1.08,
        'costreduction' => 250,
        'class' => 'Hunting'
    ]);
    install_action('Fighting - Standard', [
        'maxcost' => 2000,
        'mincost' => 500,
        'firstlvlexp' => 2000,
        'expincrement' => 1.1,
        'costreduction' => 15,
        'class' => 'Combat'
    ]);
    install_action('Running Away', [
        'maxcost' => 1000,
        'mincost' => 200,
        'firstlvlexp' => 500,
        'expincrement' => 1.05,
        'costreduction' => 8,
        'class' => 'Combat'
    ]);
    //triggers when a player loses more than 10% of his total hitpoints in a single round
    install_action('Taking It on the Chin', [
        'maxcost' => 2000,
        'mincost' => 200,
        'firstlvlexp' => 5000,
        'expincrement' => 1.1,
        'costreduction' => 15,
        'class' => 'Combat'
    ]);

    return true;
}
function staminacorecombat_uninstall()
{
    uninstall_action('Hunting - Normal');
    uninstall_action('Hunting - Big Trouble');
    uninstall_action('Hunting - Easy Fights');
    uninstall_action('Hunting - Suicidal');
    uninstall_action('Fighting - Standard');
    uninstall_action('Running Away');
    uninstall_action('Taking It on the Chin');

    return true;
}
function staminacorecombat_dohook($hookname, $args)
{
    global $session;
    static $damagestart = 0;

    $textDomain = 'module-staminacorecombat';

    $stam = \LotgdHttp::getQuery('stam');
    $op = \LotgdHttp::getQuery('op');
    $skill = \LotgdHttp::getQuery('skill');
    $auto = \LotgdHttp::getQuery('auto');

    switch ($hookname)
    {
        case 'forest':
            \LotgdNavigation::blockHideLink('forest.php?op=search');
            \LotgdNavigation::blockHideLink('forest.php?op=search&type=slum');
            \LotgdNavigation::blockHideLink('forest.php?op=search&type=thrill');
            \LotgdNavigation::blockHideLink('forest.php?op=search&type=suicide');

            $normalcost = stamina_getdisplaycost('Hunting - Normal');
            $thrillcost = stamina_getdisplaycost('Hunting - Big Trouble');

            //-- Change text domain for navigation
            \LotgdNavigation::setTextDomain('module-staminacorecombat');

            \LotgdNavigation::addHeader('category.fight', [ 'textDomain' => 'navigation-forest' ]);
            \LotgdNavigation::addNav('navigation.nav.trouble.normal', 'forest.php?op=search&stam=search', [
                'params' => [
                    'cost' => $normalcost
                ]
            ]);

            if ($session['user']['level'] > 1)
            {
                $slumcost = stamina_getdisplaycost('Hunting - Easy Fights');
                \LotgdNavigation::addNav('navigation.nav.easy', 'forest.php?op=search&type=slum&stam=slum', [
                    'params' => [
                        'cost' => $slumcost
                    ]
                ]);
            }
            \LotgdNavigation::addNav('navigation.nav.trouble.big', 'forest.php?op=search&type=thrill&stam=thrill', [
                'params' => [
                    'cost' => $thrillcost
                ]
            ]);

            if (getsetting('suicide', 0) && getsetting('suicidedk', 10) <= $session['user']['dragonkills'])
            {
                $suicidecost = stamina_getdisplaycost('Hunting - Suicidal');
                \LotgdNavigation::addNav('navigation.nav.suicidally', 'forest.php?op=search&type=suicide&stam=suicide', [
                    'params' => [
                        'cost' => $suicidecost
                    ]
                ]);
            }

            //-- Restore text domain for navigation
            \LotgdNavigation::setTextDomain();
        break;
        case 'fightnav-graveyard':
        case 'fightnav':
            $script = $args['script'];
            $fightcost = stamina_getdisplaycost('Fighting - Standard');
            $runcost = stamina_getdisplaycost('Running Away');

            \LotgdNavigation::blockHideLink($script.'op=fight');
            \LotgdNavigation::blockHideLink($script.'op=run');
            \LotgdNavigation::blockHideLink($script.'op=fight&auto=five');
            \LotgdNavigation::blockHideLink($script.'op=fight&auto=ten');

            //-- Change text domain for navigation
            \LotgdNavigation::setTextDomain('module-staminacorecombat');

            $fight = $session['user']['alive'] ? 'navigation.nav.fight.live' : 'navigation.nav.fight.death';
            $run = $session['user']['alive'] ? 'navigation.nav.run.live' : 'navigation.nav.run.death';

            \LotgdNavigation::addHeader('category.standard', [ 'textDomain' => 'navigation-fightnav' ]);
            \LotgdNavigation::addNav($fight, "{$script}op=fight&stam=fight", [
                'params' => [ 'cost' => $fightcost ]
            ]);
            \LotgdNavigation::addNav($run, "{$script}op=run&stam=run", [
                'params' => [ 'cost' => $runcost ]
            ]);

            \LotgdNavigation::addHeader('category.automatic', [ 'textDomain' => 'navigation-fightnav' ]);
            \LotgdNavigation::addNav('navigation.nav.auto.05', "{$script}op=fight&auto=five&stam=fight", [
                'params' => [ 'cost' => $fightcost * 5 ]
            ]);
            \LotgdNavigation::addNav('navigation.nav.auto.010', "{$script}op=fight&auto=ten&stam=fight", [
                'params' => [ 'cost' => $fightcost * 10 ]
            ]);

            //-- Restore text domain for navigation
            \LotgdNavigation::setTextDomain();
        break;
        case 'startofround-prebuffs':
            global $countround, $lotgdBattleContent;

            $process = (string) \LotgdHttp::getQuery('stam');

            switch ($process)
            {
                case 'search':
                    $return = process_action('Hunting - Normal');

                    if (isset($return['lvlinfo']['levelledup']) && $return['lvlinfo']['levelledup'])
                    {
                        $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [ 'action.hunting.normal', [], $textDomain ];
                    }
                break;
                case 'slum':
                    $return = process_action('Hunting - Easy Fights');

                    if (isset($return['lvlinfo']['levelledup']) && $return['lvlinfo']['levelledup'])
                    {
                        $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [ 'action.hunting.easy', [], $textDomain ];
                    }
                break;
                case 'thrill':
                    $return = process_action('Hunting - Big Trouble');

                    if (isset($return['lvlinfo']['levelledup']) && $return['lvlinfo']['levelledup'])
                    {
                        $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [ 'action.hunting.thrill', [], $textDomain ];
                    }
                break;
                case 'suicide':
                    $return = process_action('Hunting - Suicidal');

                    if (isset($return['lvlinfo']['levelledup']) && $return['lvlinfo']['levelledup'])
                    {
                        $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [ 'action.hunting.suicide', [], $textDomain ];
                    }
                break;
            }

            if (1 == $session['user']['alive'])
            {
                staminacorecombat_applystaminabuff();
            }
            $damagestart = $session['user']['hitpoints'];
        break;
        case 'endofround':
            global $countround, $lotgdBattleContent;

            $damagetaken = $damagestart - $session['user']['hitpoints'];

            if ('fight' == $stam || 'fight' == $op || ('fight' == $op && $auto) || ('fight' == $op && $skill))
            {
                $return = process_action('Fighting - Standard');

                if (isset($return['lvlinfo']['levelledup']) && $return['lvlinfo']['levelledup'])
                {
                    $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [
                        'action.fighting.standard',
                        [ 'level' => $return['lvlinfo']['newlvl'] ],
                        $textDomain
                    ];
                }
            }

            if ('run' == $stam || 'run' == $op)
            {
                $return = process_action('Running Away');

                if (isset($return['lvlinfo']['levelledup']) && $return['lvlinfo']['levelledup'])
                {
                    $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [
                        'action.fighting.running',
                        [ 'level' => $return['lvlinfo']['newlvl'] ],
                        $textDomain
                    ];
                }
            }
            $reps = ($damagetaken / $session['user']['maxhitpoints']) * 9;

            if ($reps >= 1)
            {
                $staminalost = 0;

                for ($i = 0; $i < floor($reps); $i++)
                {
                    $return = process_action('Taking It on the Chin');
                    $staminalost += $return['points_used'];

                    if (isset($return['lvlinfo']['levelledup']) && $return['lvlinfo']['levelledup'])
                    {
                        $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [
                            'action.fighting.chin',
                            [ 'level' => $return['lvlinfo']['newlvl'] ],
                            $textDomain
                        ];
                    }
                }

                $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [
                    'action.fighting.blow',
                    [ 'stamina' => $staminalost ],
                    $textDomain
                ];
            }
        break;
    }

    return $args;
}

function staminacorecombat_applystaminabuff()
{
    //increments and applies the Exhaustion Penalty
    global $session;

    $textDomain = 'module-staminacorecombat';
    $amber = get_stamina();

    if ($amber < 100)
    {
        //Gives a proportionate debuff from 1 to 0.2, at 2 decimal places each time
        $buffvalue = round(((($amber / 100) * 80) + 20) / 100, 2);

        if ($buffvalue < 0.3)
        {
            $buffmsg = 'stamina.buff.roundmsg.0.3';
        }
        elseif ($buffvalue < 0.6)
        {
            $buffmsg = 'stamina.buff.roundmsg.0.6';
        }
        elseif ($buffvalue < 0.8)
        {
            $buffmsg = 'stamina.buff.roundmsg.0.8';
        }
        elseif ($buffvalue < 1)
        {
            $buffmsg = 'stamina.buff.roundmsg.01';
        }

        if (isset($buffmsg))
        {
            apply_buff('stamina-corecombat-exhaustion', [
                'name' => \LotgdTranslator::t('stamina.buff.name', [], $textDomain),
                'atkmod' => $buffvalue,
                'defmod' => $buffvalue,
                'rounds' => -1,
                'roundmsg' => \LotgdTranslator::t($buffmsg, [], $textDomain),
                'schema' => 'module-staminacorecombat'
            ]);
        }
    }
    else
    {
        strip_buff('stamina-corecombat-exhaustion');
    }

    $red = get_stamina(0);

    if ($red < 100)
    {
        $death = e_rand(0, 100);

        if ($death > $red)
        {
            \LotgdFlashMessages::addErrorMessage(\LotgdTranslator::t('flash.message.stamina.death', [], $textDomain));

            $session['user']['hitpoints'] = 0;
            $session['user']['alive'] = false;

            return redirect('shades.php');
        }
    }

    return true;
}
