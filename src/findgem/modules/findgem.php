<?php

// translator ready
// mail ready
// addnews ready
function findgem_getmoduleinfo()
{
    return [
        'name'     => 'Find Gems',
        'version'  => '2.1.0',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
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

    ++$session['user']['gems'];

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/findgem_runevent.twig', ['textDomain' => 'module_findgem']));

    \LotgdLog::debug('found a gem in the dirt');
}

function findgem_run()
{
}
