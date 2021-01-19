<?php

/*
    I have to start by Crediting Sneakabout.  This is, blatantly, the heart, or
    at least the bowel, of his module Orb of Souls, ripped out, and placed into
    the shades.  This is here so that everybody might learn the answer to
    "what happens to dragons when they die.  Credit, as always to Saucy,
    Talisman, Deimos, JCP, Kendaer and the rest for making sure this didn't
    have completely incomprehensible and nonfunctional workings.
*/
function graveofdragons_getmoduleinfo()
{
    return [
        'name'     => 'Grave of Dragons',
        'version'  => '2.1.0',
        'author'   => 'Nightwind, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Graveyard Specials',
        'download' => 'core_module',
        'settings' => [
            'Grave of Dragons Settings,title',
            'mingold'   => 'Minimum amount of gold to find while searching,range,10,100,5|50',
            'maxgold'   => 'Maximum amount of gold to find while searching,range,150,500,10|200',
            'mingems'   => 'Minimum amount of gems to find while searching,range,1,5,1|2',
            'maxgems'   => 'Maximum amount of gems to find while searching,range,1,10,1|4',
            'lethality' => 'Percent of soulpoints to take when encountering the beast, range,10,100,10|50',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function graveofdragons_install()
{
    module_addeventhook('graveyard', 'return 100;');

    return true;
}

function graveofdragons_uninstall()
{
    return true;
}

function graveofdragons_dohook($hookname, $args)
{
    return $args;
}

function graveofdragons_runevent($type, $from)
{
    global $session;

    $from                          = 'graveyard.php?';
    $op                            = \LotgdRequest::getQuery('op');
    $session['user']['specialinc'] = 'module:graveofdragons';

    $textDomain = 'module_graveofdragons';

    $params = [
        'textDomain'    => $textDomain,
        'deathOverlord' => getsetting('deathoverlord', '`$Ramius`0'),
    ];

    \LotgdNavigation::setTextDomain($textDomain);

    if ('' == $op)
    {
        $params['tpl']      = 'default';
        $params['canEnter'] = ($session['user']['soulpoints'] > 0);

        if ($params['canEnter'])
        {
            \LotgdNavigation::addNav('navigation.nav.default.descend', $from.'op=entercavern');
        }

        \LotgdNavigation::addNav('navigation.nav.default.return', $from.'op=depart');
    }
    elseif ('depart' == $op)
    {
        $params['tpl'] = 'depart';

        $session['user']['specialinc'] = '';
    }
    elseif ('surface' == $op)
    {
        $params['tpl']                 = 'surface';
        $session['user']['specialinc'] = '';
    }
    elseif ('entercavern' == $op)
    {
        $params['tpl']       = 'entercavern';
        $params['canSearch'] = ($session['user']['gravefights'] > 0);

        if ($params['canSearch'])
        {
            \LotgdNavigation::addNav('navigation.nav.entercavern.search', $from.'op=searchcavern');
        }

        \LotgdNavigation::addNav('navigation.nav.entercavern.return', $from.'op=surface');
    }
    elseif ('searchcavern' == $op)
    {
        $params['tpl'] = 'searchcavern';
        $rand          = \mt_rand(1, 17);

        switch ($rand)
        {
            case 1:
                $session['user']['soulpoints'] -= \round(((get_module_setting('lethality')) / 100) * $session['user']['soulpoints'], 0);

                $params['soulpoints'] = $session['user']['soulpoints'];
                $params['rand']       = 1;

                if (0 == $session['user']['soulpoints'])
                {
                    $session['user']['gravefights'] = 0;
                    $session['user']['specialinc']  = '';
                }
                else
                {
                    --$session['user']['gravefights'];
                }

            break;
            case 2:
            case 3:
                $params['rand'] = 2;

                \LotgdNavigation::addNav('navigation.nav.searchcavern.take', $from.'op=takegold');
                \LotgdNavigation::addNav('navigation.nav.searchcavern.pray', $from.'op=darkaltar');
                \LotgdNavigation::addNav('navigation.nav.searchcavern.return', $from.'op=turn');
            break;
            case 4:
            case 5:
            case 6:
                $params['rand']     = 3;
                $params['randGold'] = e_rand(get_module_setting('mingold'), get_module_setting('maxgold'));

                debuglog("gained {$params['randGold']} gold from the body of a guide in the shades");

                $session['user']['gold'] += $params['randGold'];
                $session['user']['specialinc'] = '';
            break;
            case 7:
            case 8:
            case 9:
            case 10:
                $params['rand'] = 4;

                $session['user']['specialinc'] = '';
            break;
            case 11:
            case 12:
                $params['rand']     = 5;
                $params['randGold'] = e_rand(get_module_setting('mingold'), get_module_setting('maxgold'));
                $params['randGems'] = e_rand(get_module_setting('mingems'), get_module_setting('maxgems'));

                $session['user']['gems'] += $params['randGems'];
                $session['user']['gold'] += $params['randGold'];
                $session['user']['specialinc'] = '';

                debuglog("gained {$params['randGold']} gold and {$params['randGems']} gems digging in the Dragon's Graveyard.");
            break;
            case 13:
            case 14:
                $params['rand']  = 6;
                $params['favor'] = \min($session['user']['deathpower'], 5 + \mt_rand(0, $session['user']['level']));

                if ($params['favor'] > 0)
                {
                    $session['user']['deathpower'] -= $params['favor'];
                }

                $session['user']['specialinc'] = '';
            break;
            case 15:
            case 16:
            case 17:
            default:
                $params['rand'] = 7;
                $session['user']['gems'] += 2;
                $session['user']['specialinc'] = '';

                debuglog('gained 2 gems from the eye sockets of a dead dragon.');
            break;
        }
    }
    elseif ('turn' == $op)
    {
        $session['user']['specialinc'] = '';
    }
    elseif ('darkaltar' == $op)
    {
        $params['favor'] = ((get_module_setting('lethality')) + 10);

        $session['user']['deathpower'] += $params['favor'];
        $session['user']['soulpoints'] = 1;
        $session['user']['specialinc'] = '';
    }
    elseif ('takegold' == $op)
    {
        debuglog("Lost all favour from steeling from the cursed altar of {$params['deathOverlord']}.");
        $session['user']['deathpower'] = 0;
        $session['user']['specialinc'] = '';
    }

    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/graveofdragons/run.twig', $params));
}

function graveofdragons_run()
{
}
