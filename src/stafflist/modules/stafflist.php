<?php

// addnews ready
// mail ready
// translator ready
/*
Staff List
File: stafflist.php
Author:  Red Yates aka Deimos
Date:    9/8/2004
Version: 1.2 (10/3/2004)

Just a means of listing staff members. In order to put people on the list,
you have to edit their prefs for this module, setting their rank to
something more than 0. The list will be sorted by ranks, so rank them in
groups, like Moderators = 1, SrMods = 2, JrAdmin = 3, Admin = 4, SrAdmin = 5,
Owner = 6, or something like that. Or, to force an order, you could give
everyone a number, but that is kind of silly.  Anyone less than rank 1 won't
be listed. You might want to set the pref ranks before activating the module.
Also included is a space for a blurb at the bottom.

v1.1
Query optimization and such, with help from Kendaer and MightyE
v1.2
Added feature to show if staff is online (suggested by Anyanka of Central)
*/

function stafflist_getmoduleinfo()
{
    return [
        'name'           => 'Staff List',
        'version'        => '2.0.0',
        'author'         => '`$Red Yates`0, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'allowanonymous' => true,
        'category'       => 'Administrative',
        'download'       => 'core_module',
        'settings'       => [
            'Staff List Settings, title',
            'biolink'  => 'Link staff names to their bios,bool|1',
            'showdesc' => 'Show staff member description fields,bool|1',
            'showon'   => 'Show if staff member is online,bool|1',
            'blurb'    => 'Blurb to be displayed below the staff list,textarea|',
        ],
        'prefs' => [
            'Staff List User Preferences, title',
            'rank' => 'Arbitrary ranking number (higher means higher on list),int|0',
            'desc' => 'Description to be put in the staff list|I work here?',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
        ],
    ];
}

function stafflist_install()
{
    module_addhook('village');
    module_addhook('about');
    module_addhook('validatesettings');

    return true;
}

function stafflist_uninstall()
{
    return true;
}

function stafflist_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'validatesettings':
            require_once 'lib/nltoappon.php';
            $args['blurb'] = nltoappon($args['blurb']);
        break;
        case 'village':
            \LotgdNavigation::addHeader('headers.info');
            \LotgdNavigation::addNav('navigation.nav.list', 'runmodule.php?module=stafflist&from=village', ['textDomain' => 'module-stafflist']);
        break;
        case 'about':
            \LotgdNavigation::addNav('navigation.nav.list', 'runmodule.php?module=stafflist&from=about', ['textDomain' => 'module-stafflist']);
        break;
        default: break;
    }

    return $args;
}

function stafflist_run()
{
    global $session;

    $from = \LotgdRequest::getQuery('from');

    if ('about' == $from)
    {
        \LotgdNavigation::addNav('Return whence you came', 'about.php');
    }
    elseif ('village' == $from)
    {
        \LotgdNavigation::villageNav();
    }

    $textDomain = 'module-stafflist';

    \LotgdResponse::pageStart('title', [], $textDomain);

    $params = [
        'textDomain'  => $textDomain,
        'showDesc'    => get_module_setting('showdesc'),
        'showOn'      => get_module_setting('showon'),
        'showBioLink' => get_module_setting('biolink'),
    ];

    $query = \Doctrine::createQueryBuilder();
    $expr  = $query->expr();

    $params['result'] = $query->select('u.login', 'u.laston', 'u.loggedin')
        ->addSelect('r.userid', '(r.value+0) AS rango')
        ->addSelect('d.value AS descr')
        ->addSelect('c.name', 'c.sex')

        ->from('LotgdCore:Accounts', 'u')

        ->leftJoin('LotgdCore:ModuleUserprefs', 'r', 'with',
            $expr->andX(
                $expr->eq('r.modulename', $expr->literal('stafflist')),
                $expr->eq('r.setting', $expr->literal('rank')),
                $expr->eq('r.userid', 'u.acctid')
            )
        )
        ->leftJoin('LotgdCore:ModuleUserprefs', 'd', 'with',
            $expr->andX(
                $expr->eq('d.modulename', $expr->literal('stafflist')),
                $expr->eq('d.setting', $expr->literal('desc')),
                $expr->eq('d.userid', 'u.acctid')
            )
        )
        ->leftJoin('LotgdCore:Characters', 'c', 'with', $expr->eq('c.acct', 'u.acctid'))

        ->where('(r.value+0) > 0')

        ->orderBy('r.value', 'DESC')
        ->addOrderBy('u.acctid', 'ASC')

        ->getQuery()
        ->getResult()
    ;

    $params['blurb']      = get_module_setting('blurb');
    $params['returnLink'] = \LotgdRequest::getServer('REQUEST_URI');

    \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('stafflist/run.twig', $params));

    \LotgdResponse::pageEnd();
}
