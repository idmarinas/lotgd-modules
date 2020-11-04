<?php

// translator ready
//addnews ready
// mail ready
/**
 * Versions history:
 * 1.2.0: Update for work with version IDMarinas 0.8.0
 * 1.0.0: Original.
 */
function goldmine_getmoduleinfo()
{
    return [
        'name' => 'Gold Mine',
        'version' => '2.0.0',
        'author' => 'Ville Valtokari, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'settings' => [
            'Goldmine Event Settings,title',
            'alwaystether' => 'Chance the player will tether their mount automatically,range,0,100,1|10',
            'percentgemloss' => 'Percentage of gems lost on death in mine,range,0,100,1|0',
            'percentgoldloss' => 'Percentage of gold lost on death in mine,range,0,100,1|0',
        ],
        'prefs-mounts' => [
            'Goldmine Mount Preferences,title',
            'entermine' => 'Chance of entering mine,range,0,100,1|0',
            'dieinmine' => 'Chance of dying in the mine,range,0,100,1|0',
            'saveplayer' => 'Chance of saving player in mine,range,0,100,1|0',
            'tethermsg' => 'Message when mount is tethered|',
            'deathmsg' => 'Message when mount dies|',
            'savemsg' => 'Message when mount saves player|',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
        ],
    ];
}

function goldmine_install()
{
    module_addeventhook('forest', 'return 100;');

    return true;
}

function goldmine_uninstall()
{
    return true;
}

function goldmine_dohook($hookname, $args)
{
    return $args;
}

function goldmine_runevent($type)
{
    global $session, $playermount;

    $params = [
        'textDomain' => 'module-goldmine',
        'playerMount' => $playermount,
        'hasHorse' => $session['user']['hashorse'],
        'horseCanEnter' => 0,
        'horseCanDie' => 0,
        'horseCanSave' => 0,
        'mountName' => $playermount['mountname'],
        'staminaSystem' => is_module_active('staminasystem')
    ];

    if ($params['staminaSystem'])
    {
        require_once 'modules/staminasystem/lib/lib.php';
    }

    if ($params['hasHorse'])
    {
        $params['horseCanEnter'] = get_module_objpref('mounts', $params['hasHorse'], 'entermine');
        //-- See if we automatically tether
        if (mt_rand(1, 100) <= get_module_setting('alwaystether'))
        {
            $params['horseCanEnter'] = 0;
        }

        if ($params['horseCanEnter'])
        {
            // The mount cannot die or save you if it cannot enter
            $params['horseCanDie'] = get_module_objpref('mounts', $params['hasHorse'], 'dieinmine');
            $params['horseCanSave'] = get_module_objpref('mounts', $params['hasHorse'], 'saveplayer');
        }
    }

    $session['user']['specialinc'] = 'module:goldmine';
    $op = \LotgdHttp::getQuery('op');

    //-- Change text domain for navigation
    \LotgdNavigation::setTextDomain($params['textDomain']);

    if ('' == $op || 'search' == $op)
    {
        $params['tpl'] = 'default';

        \LotgdNavigation::addNav('navigation.nav.mine', 'forest.php?op=mine');
        \LotgdNavigation::addNav('navigation.nav.return', 'forest.php?op=no');
    }
    elseif ('no' == $op)
    {
        $params['tpl'] = 'no';

        $session['user']['specialinc'] = '';
    }
    elseif ('mine' == $op)
    {
        $params['tpl'] = 'mine';
        // Horsecanenter is a percent, so, if rand(1-100) > enterpercent,
        // tether it.  Set enter percent to 0 (the default), to always
        // tether.
        $params['tether'] = false;

        if (mt_rand(1, 100) > $params['horseCanEnter'] && $params['hasHorse'])
        {
            $params['tether'] = true;
            $params['tetherMsg'] = get_module_objpref('mounts', $params['hasHorse'], 'tethermsg');

            // The mount it tethered, so it cannot die nor save the player
            $params['horseCanEnter'] = 0;
            $params['horseCanDie'] = 0;
            $params['horseCanSave'] = 0;
        }

        $rand = mt_rand(1, 20);

        switch ($rand)
        {
            case 1: case 2: case 3: case 4: case 5:
                $params['mineResult'] = 1;

                if ($params['staminaSystem'])
                {
                    removestamina(25000);
                }
                else
                {
                    $session['user']['turns']--;
                }

                $session['user']['specialinc'] = '';
            break;
            case 6: case 7: case 8:case 9: case 10:
                $gold = e_rand($session['user']['level'] * 5, $session['user']['level'] * 20);
                $params['mineResult'] = 2;
                $params['goldWin'] = $gold;

                if ($params['staminaSystem'])
                {
                    removestamina(25000);
                }
                else
                {
                    $session['user']['turns']--;
                }

                debuglog("found $gold gold in the goldmine");
                $session['user']['gold'] += $gold;
                $session['user']['specialinc'] = '';
            break;
            case 11: case 12: case 13: case 14: case 15:
                $gems = e_rand(1, round($session['user']['level'] / 7) + 1);
                $params['mineResult'] = 3;
                $params['gemsWin'] = $gems;

                if ($params['staminaSystem'])
                {
                    removestamina(25000);
                }
                else
                {
                    $session['user']['turns']--;
                }

                debuglog("found $gems gems in the goldmine");
                $session['user']['gems'] += $gems;
                $session['user']['specialinc'] = '';
            break;
            case 16: case 17: case 18:
                $gold = e_rand($session['user']['level'] * 10, $session['user']['level'] * 40);
                $gems = e_rand(1, round($session['user']['level'] / 3) + 1);
                $params['mineResult'] = 4;
                $params['gemsWin'] = $gems;
                $params['goldWin'] = $gold;

                if ($params['staminaSystem'])
                {
                    removestamina(25000);
                }
                else
                {
                    $session['user']['turns']--;
                }

                debuglog("found $gold gold and $gems gems in the goldmine");
                $session['user']['gems'] += $gems;
                $session['user']['gold'] += $gold;
                $session['user']['specialinc'] = '';
            break;
            case 19: case 20:
                $params['mineResult'] = 5;

                // Find the chance of dying based on race
                $vals = modulehook('raceminedeath');
                $params['dead'] = 0;
                $params['raceSave'] = 1;
                $params['raceMsg'] = $vals['racesave'] ?? '';

                if (isset($vals['chance']) && (mt_rand(1, 100) < $vals['chance']))
                {
                    $params['dead'] = 1;
                    $params['raceSave'] = 0;
                    $params['raceMsg'] = '';
                }

                // The player has died, see if their horse saves them
                if ($params['dead'] && isset($params['horseCanSave']) && (mt_rand(1, 100) <= $params['horseCanSave']))
                {
                    $params['dead'] = 0;
                    $params['horseSave'] = 1;
                }

                // If we are still dead, see if the horse dies too.
                $session['user']['specialinc'] = '';

                if ($params['dead'])
                {
                    $params['horseDead'] = 0;

                    if (mt_rand(1, 100) <= $params['horseCanDie'])
                    {
                        $params['horseDead'] = 1;
                    }

                    if ($params['horseDead'])
                    {
                        debuglog("lost their mount, a {$playermount['mountname']}, in a mine collapse.");
                        $session['user']['hashorse'] = 0;

                        if (isset($session['bufflist']['mount']))
                        {
                            strip_buff('mount');
                        }
                    }

                    $params['expWin'] = round($session['user']['experience'] * 0.1, 0);
                    $params['gemsLost'] = round(get_module_setting('percentgemloss') / 100 * $session['user']['gems'], 0);
                    $params['goldLost'] = round(get_module_setting('percentgoldloss') / 100 * $session['user']['gold'], 0);

                    debuglog("lost {$params['goldLost']} gold and {$params['gemsLost']} gems by dying in the goldmine");

                    $session['user']['gold'] -= $params['goldLost'];
                    $session['user']['gems'] -= $params['gemsLost'];
                    $session['user']['experience'] += $params['expWin'];
                    $session['user']['alive'] = false;
                    $session['user']['hitpoints'] = 0;

                    \LotgdNavigation::addNav('navigation.nav.news', 'news.php');
                    addnews('news.dead', ['playerName' => $session['user']['name']], $params['textDomain']);
                }
                else
                {
                    debuglog('`&has lost 700000 stamina for the day due to a close call in the mine.');

                    if ($params['staminaSystem'])
                    {
                        removestamina(700000);
                    }
                    else
                    {
                        $session['user']['turns'] = 0;
                    }
                }
            break;
        }

        $session['user']['turns'] = max(0, $session['user']['turns']);
    }

    //-- Restore text domain for navigation
    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('goldmine/run.twig', $params));
}

function goldmine_run()
{
}
