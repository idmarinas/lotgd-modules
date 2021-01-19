<?php

function ppad_getmoduleinfo()
{
    return [
        'name'      => 'Paypal Area Ad',
        'version'   => '2.1.0',
        'author'    => 'Chris Vorndran, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category'  => 'Administrative',
        'download'  => 'http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=59',
        'vertxtloc' => 'http://dragonprime.net/usres/Sichae/',
        'settings'  => [
            'Paypal Area Ad Settings,title',
            'Text to be displayed to inform players why donations are allowed and/or why the game is advertised.,note',
            'This text can be translated; so it is in a .yaml file. By default text is empty,note',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function ppad_install()
{
    module_addhook_priority('everyfooter', 80);

    return true;
}

function ppad_uninstall()
{
    return true;
}

function ppad_dohook($hookname, $args)
{
    if ('everyfooter' == $hookname)
    {
        $params = [
            'textDomain' => 'module_ppad',
        ];

        $args['paypal'] = $args['paypal'] ?? '';
        $args['paypal'] .= \LotgdTheme::render('@module/ppad_everyfooter.twig', $params);
    }

    return $args;
}
function ppad_run()
{
}
