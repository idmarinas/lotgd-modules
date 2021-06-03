<?php

require_once 'modules/staminasystem/lib/lib.php';

function staminasystem_getmoduleinfo()
{
    return [
        'name'                => 'Expanded Stamina System - Core',
        'version'             => '3.3.0',
        'author'              => 'Dan Hall, aka Caveman Joe, improbableisland.com, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'override_forced_nav' => true,
        'category'            => 'Stamina',
        'download'            => '',
        'settings'            => [
            'actionsarray'            => 'Array of actions in the game that use Stamina,viewonly',
            'turns_emulation_base'    => 'Use an approximation of turns for events that are not hooked in yet - and if so then a Turn is worth at least this much Stamina (set to zero to disable),int|25000',
            'turns_emulation_ceiling' => 'One turn is worth at most this amount,int|30000',
        ],
        'prefs' => [
            'stamina'      => "Player's current Stamina,int|0",
            'red'          => 'Amount of the bar taken up in Red Stamina levels,int|300000',
            'amber'        => 'Amount of the bar taken up in Amber Stamina levels,int|500000',
            'actions'      => "Player's Actions array,textarea|a:0:{}",
            'buffs'        => "Player's Buffs array,textarea|a:0:{}",
            'user_minihof' => 'Show me the mini-HOF for Stamina-related actions,bool|true',
        ],
        'requires' => [
            'lotgd' => '>=5.0.0|Need a version equal or greater than 5.0.0 IDMarinas Edition',
        ],
    ];
}

function staminasystem_install()
{
    include 'staminasystem/installer.php';

    return true;
}

function staminasystem_uninstall()
{
    return true;
}

function staminasystem_dohook($hookname, $args)
{
    global $stamina, $session;

    include "staminasystem/dohook/switch_{$hookname}.php";

    return $args;
}

function staminasystem_run()
{
    global $session;

    $op         = \LotgdRequest::getQuery('op');
    $textDomain = 'module_staminasystem';

    include "staminasystem/run/case_{$op}.php";
}
