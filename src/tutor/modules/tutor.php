<?php

// addnews ready
// mail ready
// translator ready

function tutor_getmoduleinfo()
{
    return [
        'name'     => 'In-game tutor',
        'author'   => 'Booger & Shannon Brown & JT Traub, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '3.0.0',
        'category' => 'Administrative',
        'download' => 'core_module',
        'prefs'    => [
            'In-Game tutor User Preferences,title',
            'user_ignore' => 'Turn off the tutor help?,bool|0',
            'seenforest'  => 'Has the player seen the forest instructions,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function tutor_install()
{
    module_addhook('everyheader-loggedin');
    module_addhook('newday');
    module_addhook('village');
    module_addhook('battle');

    return true;
}

function tutor_uninstall()
{
    return true;
}

function tutor_dohook($hookname, $args)
{
    global $session;

    if ( ! $session['user']['loggedin'])
    {
        return $args;
    }

    $age    = $session['user']['age'];
    $ignore = get_module_pref('user_ignore');

    // If this person is already well out of tutoring range, just return
    if ($session['user']['dragonkills'] || $ignore || $age >= 11)
    {
        return $args;
    }

    switch ($hookname)
    {
        case 'newday':
            set_module_pref('seenforest', 0);
        break;
        case 'village':
            if ($age < 11)
            {
                \LotgdNavigation::addHeader('headers.gate');
                \LotgdNavigation::addNav('navigation.help', 'runmodule.php?module=tutor&op=helpfiles', [
                    'textDomain' => 'module_tutor',
                ]);
                \LotgdNavigation::unBlockLink('runmodule.php?module=tutor&op=helpfiles');
            }
        break;
        case 'battle':
            global $badguy;

            $tutormsg = '';

            if ($badguy['creaturehealth'] > 0 && $badguy['creaturelevel'] > $session['user']['level'] && 'forest' == $badguy['type'])
            {
                tutor_talk('message.battle');
            }
        // no break
        case 'everyheader-loggedin':
            $adef       = $session['user']['armordef'];
            $wdam       = $session['user']['weapondmg'];
            $gold       = $session['user']['gold'];
            $goldinbank = $session['user']['goldinbank'];
            $goldtotal  = $gold + $goldinbank;

            if ( ! isset($args['script']) || ! $args['script'])
            {
                break;
            }

            switch ($args['script'])
            {
                case 'newday':
                    if ($age > 1)
                    {
                        break;
                    }

                    if (( ! $session['user']['race'] || RACE_UNKNOWN == $session['user']['race']) && '' == \LotgdRequest::getQuery('setrace'))
                    {
                        if (is_module_active('racetroll') || is_module_active('racedwarf') || is_module_active('racehuman') || is_module_active('raceelf'))
                        {
                            tutor_talk('message.newday.race');
                        }
                    }
                    elseif ('' == $session['user']['specialty'] && ! \LotgdRequest::getQuery('setrace'))
                    {
                        if (is_module_active('specialtylaser') || is_module_active('specialtytelepathy') || is_module_active('specialtytelekinesis') || is_module_active('specialtyspacialawareness'))
                        {
                            tutor_talk('message.newday.race');
                        }
                    }
                break;
                case 'village':
                    $tutormsg = '';

                    if (0 == $wdam && $gold >= 50)
                    {
                        $tutormsg = 'message.village.weapon.gold';
                    }
                    elseif (0 == $wdam && $goldtotal >= 50)
                    {
                        $tutormsg = 'message.village.weapon.bank';
                    }
                    elseif (0 == $adef && $gold >= 60)
                    {
                        $tutormsg = 'message.village.armor.gold';
                    }
                    elseif (0 == $adef && $goldtotal >= 60)
                    {
                        $tutormsg = 'message.village.armor.bank';
                    }
                    elseif ( ! $session['user']['experience'])
                    {
                        $tutormsg = 'message.village.exp';
                    }
                    elseif ($session['user']['experience'] > 100 && 1 == $session['user']['level'] && ! $session['user']['seenmaster'])
                    {
                        $tutormsg = 'message.village.level';
                    }

                    if ($tutormsg)
                    {
                        tutor_talk($tutormsg);
                    }
                break;
                case 'forest':
                    $tutormsg = '';

                    if ($goldtotal >= 50 && 0 == $wdam)
                    {
                        $tutormsg = 'message.forest.weapon';
                    }
                    elseif ($goldtotal >= 60 && 0 == $adef)
                    {
                        $tutormsg = 'message.forest.armor';
                    }
                    elseif ( ! $session['user']['experience'] && ! get_module_pref('seenforest'))
                    {
                        $tutormsg = 'message.seen.forest';
                        set_module_pref('seenforest', 1);
                    }

                    if ($tutormsg)
                    {
                        tutor_talk($tutormsg);
                    }
                break;
                default: break;
            }
        break;
    }

    return $args;
}

function tutor_talk()
{
    $args = \func_get_args();
    $text = \array_shift($args);

    $params = [
        'textDomain' => 'module_tutor',
        'message'    => [
            $text,
            \is_array($args) ? $args : [],
        ],
    ];

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/tutor/talk.twig', $params));
}

function tutor_run()
{
    global $session;

    $op    = \LotgdRequest::getQuery('op');
    $city  = LotgdSetting::getSetting('villagename', LOCATION_FIELDS); // name of capital city
    $iname = LotgdSetting::getSetting('innname', LOCATION_INN); // name of capital's inn
    $age   = $session['user']['age'];

    if ('helpfiles' == $op)
    {
        \LotgdResponse::pageStart('run.title', [], 'module_tutor');

        $params = [
            'textDomain' => 'module_tutor',
            'city'       => $city,
            'iname'      => $iname,
            'age'        => $age,
        ];

        \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/tutor/run.twig', $params));

        \LotgdNavigation::villageNav();

        \LotgdResponse::pageEnd();
    }
}
