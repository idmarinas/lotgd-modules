<?php

/* Village Klutz ver 1.0 12th Sept 2004 => SaucyWench -at- gmail -dot- com */

function klutz_getmoduleinfo()
{
    return [
        'name' => 'Village Klutz',
        'version' => '2.0.0',
        'author' => 'Shannon Brown, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village Specials',
        'download' => 'core_module',
        'prefs' => [
            'Klutz User Preferences,title',
            'gotgem' => 'Has player received a gem today?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function klutz_install()
{
    module_addhook('newday');
    module_addeventhook('village', "return (max(1,(200-\$session['user']['dragonkills'])/2));");

    return true;
}

function klutz_uninstall()
{
    return true;
}

function klutz_dohook($hookname, $args)
{
    global $session;

    if ('newday' == $hookname)
    {
        set_module_pref('gotgem', 0);
    }

    return $args;
}

function klutz_runevent($type)
{
    global $session;

    $params = [
        'textDomain' => 'module-klutz'
    ];

    if (0 == get_module_pref('gotgem') && 1 == mt_rand(1, 4))
    {
        $params['gem'] = true;

        $session['user']['gems']++;
        set_module_pref('gotgem', 1);
    }

    rawoutput(LotgdTheme::renderModuleTemplate('klutz/runevent.twig', $params));
}

function klutz_run()
{
}
