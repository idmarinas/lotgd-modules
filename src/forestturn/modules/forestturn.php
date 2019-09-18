<?php

// translator ready
// mail ready
// addnews ready
function forestturn_getmoduleinfo()
{
    return [
        'name' => 'Forest Turn win/lose',
        'version' => '2.0.0',
        'author' => 'JT Traub<br>based on code from 4winz, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'settings' => [
            'Forest Turn Event Settings,title',
            'percentgain' => 'Chance to gain a turn (otherwise lose),range,0,100,1|55'
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function forestturn_install()
{
    module_addeventhook('forest', 'return 100;');

    return true;
}

function forestturn_uninstall()
{
    return true;
}

function forestturn_dohook($hookname, $args)
{
    return $args;
}

function forestturn_runevent($type)
{
    global $session;
    // The only type of event we care about are the forest.
    $chance = (int) get_module_setting('percentgain');
    $roll = mt_rand(1, 100);

    $params = [
        'textDomain' => 'module-forestturn',
        'win' => ($roll <= $chance)
    ];

    if ($params['win'])
    {
        $session['user']['turns']++;
    }
    else
    {
        if ($session['user']['turns'] > 0)
        {
            $params['lose'] = true;
            $session['user']['turns']--;
        }
    }

    rawoutput(LotgdTheme::renderModuleTemplate('forestturn/runevent.twig', $params));
}

function forestturn_run()
{
}
