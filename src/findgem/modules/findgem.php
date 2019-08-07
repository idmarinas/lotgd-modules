<?php

// translator ready
// mail ready
// addnews ready
function findgem_getmoduleinfo()
{
    return [
        'name' => 'Find Gems',
        'version' => '2.0.0',
        'author' => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
            'cities' => '>=2.0.0|Multiple Cities - Core module'
        ]
    ];
}

function findgem_install()
{
    module_addeventhook('forest', 'return 100;');
    module_addeventhook('travel', 'return 20;');

    return true;
}

function findgem_uninstall()
{
    return true;
}

function findgem_dohook($hookname, $args)
{
    return $args;
}

function findgem_runevent($type, $link)
{
    global $session;

    $session['user']['gems']++;

    rawoutput(\LotgdTheme::renderModuleTemplate('cityprefs/run.twig', [ 'textDomain' => 'module-findgem' ]));

    debuglog('found a gem in the dirt');
}

function findgem_run()
{
}
