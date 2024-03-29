<?php

/**
 * Modified by MarcTheSlayer
 *
 * 05/02/2009 - v1.0b
 * Added the 'editid' setting to store the id of the last edited user.
 */
function allprefseditor_getmoduleinfo()
{
    return [
        'name'     => 'Allprefs Editor',
        'version'  => '2.1.0',
        'author'   => 'DaveS, modified by `@MarcTheSlayer`0, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Administrative',
        'download' => '',
        'settings' => [
            'Allprefs Editor Prefs,title',
            'editid' => 'ID of the last person to be edited.,int|1',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function allprefseditor_install()
{
    module_addhook('superuser');

    return true;
}

function allprefseditor_uninstall()
{
    return true;
}

function allprefseditor_dohook($hookname, $args)
{
    global $session;

    $textDomain = 'module_allprefseditor';

    $id = LotgdRequest::getQuery('userid');

    if ( ! $id)
    {
        $id = get_module_setting('editid', 'allprefseditor');
    }

    if ($hookname === 'superuser' && ($session['user']['superuser'] & SU_EDIT_USERS) !== 0)
    {
        LotgdNavigation::addHeader('superuser.category.editors', ['textDomain' => 'navigation_app']);
        LotgdNavigation::addNav('navigation.nav.editors', "runmodule.php?module=allprefseditor&userid={$id}", [
            'textDomain' => $textDomain,
        ]);
    }

    return $args;
}

function allprefseditor_run()
{
    $id = LotgdRequest::getQuery('userid');

    if ( ! $id)
    {
        $id = get_module_setting('editid', 'allprefseditor');
    }

    set_module_setting('editid', $id, 'allprefseditor');

    $textDomain = 'module_allprefseditor';

    LotgdResponse::pageStart('title', [], $textDomain);

    LotgdNavigation::superuserGrottoNav();

    LotgdNavigation::addNav('navigation.nav.edit', "user.php?op=edit&userid={$id}", [
        'textDomain' => $textDomain,
    ]);

    modulehook('allprefs');

    $repository = Doctrine::getRepository('LotgdCore:Avatar');
    $params     = [
        'textDomain' => $textDomain,
        'name'       => $repository->getCharacterNameFromAcctId($id),
        'formSearch' => "runmodule.php?module=allprefseditor&subop1=search&userid={$id}",
        'isSearch'   => ('search' == LotgdRequest::getQuery('subop1')),
    ];

    if ($params['isSearch'])
    {
        $name = LotgdRequest::getPost('name');

        $params['paginator'] = $repository->findLikeName($name, 100);
    }

    LotgdResponse::pageAddContent(LotgdTheme::render('@module/allprefseditor_run.twig', $params));

    LotgdResponse::pageEnd();
}

function allprefseditor_search()
{
}
