<?php
/*
Module Name:  Alignment Events
Category:  Forest Specials
Author:  CortalUX, modified by DaveS and Selenity Hyperion
*/

function alignmentevents_getmoduleinfo()
{
    return [
        'name'     => 'Alignment Events',
        'version'  => '4.1.0',
        'author'   => '`@CortalUX`7, modified by DaveS and `0`b`&Se`0`)le`0`4nity`0 `&Hy`0`)pe`0`4rion`0´b, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=334',
        'settings' => [
            'alignbad'  => 'Alignment points lost being evil, int|1',
            'aligngood' => 'Alignment points gained for being good, int|1',
        ],
        'prefs' => [
            'Alignment Events - Preferences,title',
            'aligntried' => 'Had a chance to help someone this newday?,bool|0',
        ],
        'requires' => [
            'alignment' => '>=2.0.0|Chris Vorndran, WebPixie',
            'lotgd'     => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function alignmentevents_chance()
{
    global $session;

    if (1 == get_module_pref('aligntried', 'alignmentevents', $session['user']['acctid']))
    {
        return 0;
    }

    return 100;
}

function alignmentevents_install()
{
    module_addeventhook('forest', 'require_once("modules/alignmentevents.php"); return alignmentevents_chance();');
    module_addhook('newday');

    return true;
}

function alignmentevents_uninstall()
{
    return true;
}

function alignmentevents_dohook($hookname, $args)
{
    global $session;

    if ('newday' == $hookname)
    {
        set_module_pref('aligntried', 0);
    }

    return $args;
}

function alignmentevents_runevent($type)
{
    global $session;

    $op    = LotgdRequest::getQuery('op');
    $event = (int) LotgdRequest::getQuery('op2');

    $params = [
        'textDomain' => 'module_alignmentevents',
        'playerName' => $session['user']['name'],
    ];

    //-- Change text domain for navigation
    \LotgdNavigation::setTextDomain($params['textDomain']);

    if ('' == $op)
    {
        $params['tpl']   = 'default';
        $params['event'] = \mt_rand(1, 12);

        $session['user']['specialinc'] = 'module:alignmentevents';
        set_module_pref('aligntried', 1);

        \LotgdNavigation::addHeader('navigation.category.actions');
        \LotgdNavigation::addNav('navigation.nav.help', "forest.php?op=help&op2={$params['event']}");
        \LotgdNavigation::addNav('navigation.nav.hinder', "forest.php?op=hinder&op2={$params['event']}");
        \LotgdNavigation::addNav('navigation.nav.ignore', "forest.php?op=ignore&op2={$params['event']}");
    }
    elseif ('help' == $op)
    {
        $params['tpl'] = 'help';

        increment_module_pref('alignment', get_module_setting('aligngood'), 'alignment');

        addnews("news.event.help.0{$event}", $params, $params['textDomain']);

        $session['user']['specialinc'] = '';
    }
    elseif ('hinder' == $op)
    {
        $params['tpl'] = 'hinder';

        increment_module_pref('alignment', -get_module_setting('alignbad'), 'alignment');

        addnews("news.event.help.0{$event}", $params, $params['textDomain']);

        $session['user']['specialinc'] = '';
    }
    elseif ('ignore' == $op)
    {
        $params['tpl'] = 'ignore';

        addnews("news.event.help.0{$event}", $params, $params['textDomain']);

        $session['user']['specialinc'] = '';
    }

    //-- Restore text domain for navigation
    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/alignmentevents/runevent.twig', $params));
}
function alignmentevents_run()
{
}
