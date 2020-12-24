<?php

function statue_getmoduleinfo()
{
    return [
        'name'      => 'Village Statue',
        'author'    => '`%Simon Welsh`0<br>`#Based on Village Statue by Eric Stevens`0, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'   => '3.0.0',
        'category'  => 'Village',
        'download'  => 'http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=667',
        'vertxtloc' => 'http://simon.geek.nz/',
        'settings'  => [
            'Village Statue Settings,title',
            'hero'        => 'Who is the main statue of? (ID),int|',
            'heroName'    => 'Who is the statue of? (Name)|',
            'villagehero' => 'Who is the statue of in every village?,viewonly|'.\serialize([]),
            'days'        => 'How many days until a new statue is created?,range,1,10,1|5',
            'time'        => 'How many days has the statue been under construction for?,int|0',
            'showonindex' => 'Should the last dragonkill be shown on the home page?,bool|1',
            'Destroy Statue Settings,title',
            'adestroy'  => 'Allow user to destroy statue?,bool|1',
            'stocks'    => 'If user breaks statue put them in the stocks?,bool|1',
            'chance'    => 'Chance of breaking the statue? (%),range,1,100,1|30',
            'gold'      => 'Lose gold when breaking statue?,bool|0',
            'gems'      => 'Lose gems when breaking statue?,bool|0',
            'charm'     => 'Amount of charm to loose,int|5',
            'hitpoints' => '% of hitpoints to lose,range,1,100,1|30',
        ],
        'prefs' => [
            'Village Statue - User Prefs,title',
            'see' => 'Show Statue for user,bool|1',
        ],
    ];
}
function statue_install()
{
    module_addhook('village-desc');
    module_addhook('dragonkill');
    module_addhook('namechange');
    module_addhook('page-home-tpl-params');
    module_addhook('newday-runonce');

    return true;
}
function statue_uninstall()
{
    \LotgdResponse::pageDebug('Uninstalling module.');

    return true;
}
function statue_dohook($hookname, $args)
{
    global $session;

    $capital  = getsetting('villagename', LOCATION_FIELDS);
    $hero     = get_module_setting('hero');
    $heroName = get_module_setting('heroName');
    $heros    = \unserialize(get_module_setting('villagehero'));
    $time     = get_module_setting('time');
    $day      = get_module_setting('days');
    $see      = get_module_pref('see');
    $chance   = get_module_setting('chance');

    if ($hero && ! $heroName)
    {
        $repository = \Doctrine::getRepository('LotgdCore:Characters');
        $entity     = $repository->find($hero);

        if ($entity)
        {
            $heroName = $entity->getName();

            set_module_setting('heroName', $heroName, 'statue');
        }

        unset($entity);
    }

    $heroName = $heroName ?: 'MightyE';

    switch ($hookname)
    {
        case 'newday-runonce':
            \LotgdResponse::pageDebug('Adding time to main');
            increment_module_setting('time');
            \LotgdResponse::pageDebug("\$time = {$time}");

            foreach ($heros as $what => $city)
            {
                \LotgdResponse::pageDebug("Adding time to {$what}");
                ++$heros[$what]['time'];
                \LotgdResponse::pageDebug("\$city['time'] = {$city['time']}");
            }
        break;
        case 'village-desc':
            //-- User can't see statue
            if ( ! $see)
            {
                return $args;
            }

            $params = [
                'location'   => $session['user']['location'],
                'time'       => $time,
                'hero'       => $hero,
                'heroName'   => $heroName,
                'playerName' => $session['user']['name'],
            ];

            if ($session['user']['location'] != $capital)
            {
                $params['time']     = 0;
                $params['heroName'] = 'MightyE';

                if (isset($heros[$session['user']['location']]['hero']))
                {
                    $params['hero']     = $heros[$session['user']['location']]['hero'];
                    $params['heroName'] = $heros[$session['user']['location']]['heroName'];
                    $params['time']     = $heros[$session['user']['location']]['time'];
                }
            }

            $op = (string) \LotgdRequest::getQuery('op');

            //-- Process examine statue
            if ('astatue' == $op)
            {
                if (\mt_rand(1, 100) <= $chance)
                {
                    require_once 'lib/partner.php';

                    $params['partner'] = get_partner();

                    $args[] = ['section.village.examine.break.closer', $params, 'module-statue'];

                    $text = 'section.village.examine.break.remember';

                    if (get_module_setting('stocks') && is_module_active('stocks') && $session['user']['location'] == $capital)
                    {
                        $text = 'section.village.examine.break.partner';

                        set_module_setting('victim', $session['user']['acctid'], 'stocks');
                    }
                    elseif ($session['user']['location'] == $capital)
                    {
                        $text = 'section.village.examine.break.capital';
                    }

                    $args[] = [$text, $params, 'module-statue'];

                    addnews('news.broke.statue', [
                        'playerName' => $session['user']['name'],
                        'location'   => $session['user']['location'],
                    ], 'module-statue');

                    $debuglog = 'Lost ';

                    if (get_module_setting('gold'))
                    {
                        $debuglog .= "{$session['user']['gold']} gold, ";
                        $session['user']['gold'] = 0;
                        $args[]                  = ['section.village.examine.break.lost.gold', $params, 'module-statue'];
                    }

                    if (get_module_setting('gems'))
                    {
                        $debuglog .= "{$session['user']['gems']} gems, ";
                        $session['user']['gems'] = 0;
                        $args[]                  = ['section.village.examine.break.lost.gems', $params, 'module-statue'];
                    }

                    if (get_module_setting('charm'))
                    {
                        $charm = \min(get_module_setting('charm'), $session['user']['charm']);
                        $debuglog .= "{$charm} charm, ";
                        $session['user']['charm'] -= $charm;
                        $args[] = ['section.village.examine.break.lost.charm', $params, 'module-statue'];
                    }

                    if (get_module_setting('hitpoints'))
                    {
                        $hitpoints = \floor((get_module_setting('hitpoints') / 100) * $session['user']['hitpoints']);
                        $hitpoints = \min($session['user']['hitpoints'] - 1, $hitpoints);

                        $debuglog .= "{$hitpoints} hitpoints, ";
                        $session['user']['hitpoints'] -= $hitpoints;
                        $args[] = ['section.village.examine.break.lost.hitpoints', $params, 'module-statue'];
                    }

                    $array    = modulehook('statue-broke', ['lost' => $debuglog]);
                    $debuglog = $array['lost'];
                    $debuglog .= "for breaking the statue in {$session['user']['location']}.";
                    debuglog($debuglog);

                    if ($session['user']['location'] == $capital)
                    {
                        set_module_setting('time', 0);
                    }
                    else
                    {
                        $heros[$session['user']['location']]['time'] = 0;
                    }

                    set_module_setting('villagehero', \serialize($heros));

                    return $args;
                }

                $args[] = ['section.village.examine.view', $params, 'module-statue'];
            }

            //-- Show statue
            $text = 'section.village.hero.yes.erected';

            if ( ! $hero && $params['time'] >= \floor($day / 2))
            {
                $text = 'section.village.hero.no.erected';
            }
            elseif ( ! $hero && $params['time'] < \floor($day / 2))
            {
                $text = 'section.village.hero.no.erecting';
            }
            elseif ($params['time'] < $day)
            {
                $text = 'section.village.hero.yes.erecting';
            }

            $args[] = [$text, $params, 'module-statue'];
            $args[] = ['section.village.hero.examine', $params, 'module-statue'];
            \LotgdNavigation::addNavAllow('village.php?op=astatue');
        break;
        case 'page-home-tpl-params':
            if ( ! get_module_setting('showonindex'))
            {
                break;
            }

            $args['includeTemplatesIndex']['module/statue/dohook/home.twig'] = [
                'textDomain' => 'module-statue',
                'heroName'   => $heroName,
                'hero'       => $hero,
            ];
        break;
        case 'dragonkill':
            if (get_module_setting('hero') != $session['user']['acctid'])
            {
                set_module_setting('time', '0');
                set_module_setting('hero', $session['user']['acctid']);
                set_module_setting('heroName', $session['user']['name']);
            }

            if (is_module_active('cities'))
            {
                $homecity = get_module_pref('homecity', 'cities');

                if ( ! isset($heros[$homecity]) || $heros[$homecity]['hero'] != $session['user']['acctid'])
                {
                    $heros[$homecity] = [
                        'hero'     => $session['user']['acctid'],
                        'heroName' => $session['user']['name'],
                        'time'     => 0,
                    ];
                }
            }
        break;
        case 'namechange':
            if ($hero == $session['user']['acctid'])
            {
                set_module_setting('heroName', $session['user']['name'], 'statue');
            }

            foreach ($heros as $city => $data)
            {
                if ($data['hero'] != $session['user']['acctid'])
                {
                    continue;
                }

                $heros[$city]['heroName'] = $session['user']['name'];
            }
        break;
    }

    set_module_setting('villagehero', \serialize($heros));

    return $args;
}
