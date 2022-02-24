<?php

// translator ready
// addnews ready
// mail ready

function drunkard_getmoduleinfo()
{
    return [
        'name'     => 'Drunkard',
        'version'  => '3.0.0',
        'author'   => 'JT Traub<br>w/ mods suggested by Jason Still, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Inn Specials',
        'download' => 'core_module',
        'settings' => [
            'Drunkard Event Settings,title',
            'spillchance' => 'Chance that the drunk spills beer on you.,range,0,100,1|30',
            'maxseen'     => 'How many times per day (0 unlimited),int|2',
        ],
        'prefs' => [
            'Drunkard User Preferences,title',
            'seen' => 'How many times has the drunkard been seen?,int|0',
        ],
        'requires' => [
            'lotgd' => '>=6.0.0|Need a version equal or greater than 6.0.0 IDMarinas Edition',
        ],
    ];
}

function drunkard_chance()
{
    if (get_module_pref('seen', 'drunkard') < get_module_setting('maxseen', 'drunkard'))
    {
        return 100;
    }

    return 0;
}

function drunkard_install()
{
    module_addeventhook('inn', 'require_once("modules/drunkard.php"); return drunkard_chance();');
    module_addhook('newday');

    return true;
}

function drunkard_uninstall()
{
    return true;
}

function drunkard_dohook($hookname, $args)
{
    if ('newday' == $hookname)
    {
        set_module_pref('seen', 0);
    }

    return $args;
}

function drunkard_runevent($type)
{
    global $session;

    require_once 'lib/partner.php';

    $chance = get_module_setting('spillchance');
    $roll   = \mt_rand(1, 100);
    $seen   = get_module_pref('seen');
    set_module_pref('seen', $seen + 1);

    $textDomain = 'module_drunkard';

    $params = [
        'textDomain' => $textDomain,
        'lucky'      => false,
        'partner'    => LotgdTool::getPartner(),
    ];

    if ($roll < $chance)
    {
        // He spills on you
        --$session['user']['charm'];
        $session['user']['charm'] = \max(0, $session['user']['charm']);
    }
    else
    {
        $params['lucky'] = true;
        // You're safe
        ++$session['user']['charm'];
    }

    LotgdResponse::pageAddContent(LotgdTheme::render('@module/drunkard_runevent.twig', $params));
}

function drunkard_run()
{
}
