<?php

function cemetery_getmoduleinfo()
{
    return [
        'name'     => 'Cemetery Spook Module',
        'author'   => 'JT Traub & S Brown, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village',
        'version'  => '3.0.0',
        'download' => 'core_module',
        'settings' => [
            'Cemetery Settings,title',
            'cemeteryloc' => 'Where does the cemetery appear,location|'.LotgdSetting::getSetting('villagename', LOCATION_FIELDS),
        ],
        'requires' => [
            'lotgd'  => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
            'cities' => '3.1.0|Eric Stevens, part of the core distribution',
        ],
    ];
}

function cemetery_install()
{
    module_addhook('footer-shades');
    module_addhook('village');
    module_addhook('commentary');
    module_addhook('changesetting');

    return true;
}

function cemetery_uninstall()
{
    return true;
}

function cemetery_dohook($hookname, $args)
{
    global $session;

    $cemeteryloc = get_module_setting('cemeteryloc');

    switch ($hookname)
    {
        case 'changesetting':
            if ('villagename' == $args['setting'] && $args['old'] == $cemeteryloc)
            {
                set_module_setting('cemeteryloc', $args['new']);
            }
        break;
        case 'footer-shades':
            LotgdNavigation::addHeader('category.places');
            LotgdNavigation::addNav('nav.haunt', 'runmodule.php?module=cemetery&op=deadspeak', [
                'textDomain' => 'cemetery_navigation',
                'params'     => ['location' => $cemeteryloc],
            ]);
        break;
        case 'village':
            if ($session['user']['location'] == $cemeteryloc)
            {
                LotgdNavigation::addHeader('headers.gate');
                LotgdNavigation::addNav('nav.cemetery', 'runmodule.php?module=cemetery&op=cemetery', ['textDomain' => 'cemetery_navigation']);
            }
        break;
    }

    return $args;
}

function cemetery_run()
{
    global $session;

    $village = get_module_setting('cemeteryloc');
    $op      = (string) LotgdRequest::getQuery('op');

    $params = [
        'village'    => $village,
        'tpl'        => 'cemetery',
        'textDomain' => 'cemetery_module',
    ];

    LotgdResponse::pageStart('title', [], $params['textDomain']);

    LotgdNavigation::villageNav();

    if ('deadspeak' == $op)
    {
        $params['tpl']        = 'village';
        $params['textDomain'] = 'cemetery_village';

        LotgdResponse::pageStart('title', ['village' => $village], $params['textDomain']);
    }

    LotgdResponse::pageAddContent(LotgdTheme::render('@module/cemetery/commentary.twig', $params));

    LotgdResponse::pageEnd();
}
