<?php

// translator ready
// addnews ready
// mail ready

/*Weather, version 2.5
- Added weather display in gardens
- Added climate for shades
*/
function weather_getmoduleinfo()
{
    return [
        'name'     => 'Weather',
        'author'   => '`4Talisman`0, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '4.0.0',
        'category' => 'General',
        'download' => 'core_module',
        'settings' => [
            'Normal Weather Settings,title',
            'wxreport' => "Village weather message|`n`&Today's weather is expected to be `^%s`&.`n",
            'weather'  => 'Current Weather,int|6',
            'Micro Climate Settings, title',
            'enablemicro'   => 'Enable Unique Climate Location,bool|0',
            'microloc'      => 'Unique Climate Location,location|'.LotgdSetting::getSetting('villagename', LOCATION_FIELDS),
            'microwxreport' => 'Unique Climate weather message|`n`&The weather elf is predicting `^%s`& today.`n',
            'microwx'       => 'Current Weather,int|1',
            'Shades Weather Settings,title',
            'enableshades'   => 'Enable Shades Climate Conditions,bool|1',
            'shadeswxreport' => 'Shades weather message|`n`7The atmosphere in Shades is currently `^%s`&.`n`n',
            'shadeswx'       => 'Current Weather,int|2',
            'counter'        => 'Hell Has Frozen over counter,int|0',
        ],
        'prefs' => [
            'Weather in Shades User Setting, title',
            'gotfight' => 'Received extra torment today,int|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function weather_install()
{
    module_addhook('newday-runonce');
    module_addhook_priority('newday', 1000);
    module_addhook('village-desc');
    module_addhook('page-gardens-tpl-params');
    module_addhook('page-shades-tpl-params');
    module_addhook('page-home-tpl-params');

    if ( ! get_module_setting('updated', 'weather'))
    {
        set_module_setting('weather', \mt_rand(1, 8));
        set_module_setting('microwx', \mt_rand(1, 8));

        $shadeswx = \mt_rand(1, 8);

        if (5 == $shadeswx)
        {
            increment_module_setting('counter', 1, 'weather');
        }

        set_module_setting('shadeswx', $shadeswx);
    }

    return true;
}

function weather_uninstall()
{
    return true;
}

function weather_dohook($hookname, $args)
{
    global $session;

    $textDomain = 'module_weather';

    switch ($hookname)
    {
        case 'newday-runonce':
            set_module_setting('weather', \mt_rand(1, 8));
            set_module_setting('microwx', \mt_rand(1, 8));

            $shadeswx = \mt_rand(1, 8);

            if (5 == $shadeswx)
            {
                increment_module_setting('counter', 1, 'weather');
            }

            set_module_setting('shadeswx', $shadeswx);
        break;
        case 'newday':
            if ((5 == get_module_setting('shadeswx')) && (get_module_setting('counter') > get_module_pref('gotfight')))
            {
                set_module_pref('gotfight', get_module_setting('counter'));
                ++$session['user']['gravefights'];
            }

            $args['includeTemplatesPost']['@module/weather/dohook/newday.twig'] = [
                'textDomain'    => $textDomain,
                'weatherNormal' => get_module_setting('weather'),
                'weatherMicro'  => get_module_setting('microwx'),
                'weatherShades' => get_module_setting('shadeswx'),
                'gotFight'      => (5 == get_module_setting('shadeswx') && get_module_setting('counter') > get_module_pref('gotfight')),
                'counter'       => get_module_setting('counter'),
                'deathOverlord' => LotgdSetting::getSetting('deathoverlord', '`$Ramius`0'),
            ];
        break;
        case 'page-gardens-tpl-params':
            $enablemicro = get_module_setting('enablemicro', 'weather');
            $microloc    = get_module_setting('microloc', 'weather');

            $params = [
                'textDomain' => $textDomain,
                'weather'    => get_module_setting('weather', 'weather'),
                'type'       => 'normal',
            ];

            if (($microloc == $session['user']['location']) && (1 == $enablemicro))
            {
                $params['type']    = 'micro';
                $params['weather'] = get_module_setting('microwx');
            }

            $args['includeTemplatesPost']['@module/weather/weather.twig'] = $params;
        break;
        case 'village-desc':
            $enablemicro = get_module_setting('enablemicro', 'weather');
            $microloc    = get_module_setting('microloc', 'weather');

            $params = [
                'textDomain' => $textDomain,
                'weather'    => get_module_setting('weather', 'weather'),
                'type'       => 'normal',
            ];

            if (($microloc == $session['user']['location']) && (1 == $enablemicro))
            {
                $params['type']    = 'micro';
                $params['weather'] = get_module_setting('microwx', 'weather');
            }

            $args[] = LotgdTheme::render('@module/weather/weather.twig', $params);
        break;
        case 'page-home-tpl-params':
            $args['includeTemplatesIndex']['@module/weather/weather.twig'] = [
                'textDomain' => $textDomain,
                'weather'    => get_module_setting('weather', 'weather'),
                'type'       => 'normal',
            ];
        break;
        case 'page-shades-tpl-params':
            $args['includeTemplatesPost']['@module/weather/weather.twig'] = [
                'textDomain' => $textDomain,
                'weather'    => get_module_setting('shadeswx', 'weather'),
                'type'       => 'shades',
            ];
        break;
    }

    return $args;
}

function weather_runevent($type)
{
}

function weather_run()
{
}
