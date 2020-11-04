<?php

// translator ready
// mail ready
// addnews ready
function findgold_getmoduleinfo()
{
    return [
        'name' => 'Find Gold',
        'version' => '2.0.0',
        'author' => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'settings' => [
            'Find Gold Event Settings,title',
            'mingold' => 'Minimum gold to find (multiplied by level),range,0,50,1|10',
            'maxgold' => 'Maximum gold to find (multiplied by level),range,20,150,1|50'
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function findgold_install()
{
    module_addeventhook('forest', 'return 100;');
    module_addeventhook('travel', 'return 20;');

    return true;
}

function findgold_uninstall()
{
    return true;
}

function findgold_dohook($hookname, $args)
{
    return $args;
}

function findgold_runevent($type, $link)
{
    global $session;
    $min = $session['user']['level'] * get_module_setting('mingold');
    $max = $session['user']['level'] * get_module_setting('maxgold');
    $gold = mt_rand(min($min, $max), max($min, $max));

    $session['user']['gold'] += $gold;

    debuglog("found $gold gold in the dirt");

    \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('findgold/runevent.twig', ['textDomain' => 'module-findgold', 'gold' => $gold]));
}

function findgold_run()
{
}
