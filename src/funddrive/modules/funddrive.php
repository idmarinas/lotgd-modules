<?php

/**
 * 1.2.0
 * Add -> Currency simbol personalizate
 * Add -> Position of simbol (left of right of amount.
 */
function funddrive_getmoduleinfo()
{
    return [
        'name' => 'Fund Drive Indicator',
        'version' => '2.0.0',
        'author' => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Administrative',
        'download' => 'core_module',
        'settings' => [
            'baseamount' => 'Base amount (positive for donations not registered with the site / negative for expenses),int|0',
            'goalamount' => 'Goal amount of profit,int|5000',
            'simbol' => 'Simbol of currency,var|$',
            'simbolPosition' => 'Currency simbol before amount?,bool|1',
            'targetmonth' => "Month which we're watching,enum,,Always the current month,01,January,02,February,03,March,04,April,05,May,06,June,07,July,08,August,09,September,10,October,11,November,12,December|",
            'usebar' => 'Graph display:,enum,0,None,1,Bar,2,Graphic|1',
            'usetext' => 'Should we display the text as well?,bool|1',
            'showdollars' => 'Display dollar amounts in the text?,bool|1',
            'deductfees' => 'Should the paypal fees be deducted from the amount?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function funddrive_install()
{
    module_addhook('everyfooter');
    module_addhook('donation');

    return true;
}

function funddrive_uninstall()
{
    return true;
}

function funddrive_dohook($hookname, $args)
{
    global $html;

    require_once 'modules/funddrive/lib.php';

    if ('everyfooter' == $hookname)
    {
        $prog = funddrive_getpercent();

        $params = [
            'textDomain' => 'module-funddrive',
            'percent' => $prog['percent'],
            'goal' => $prog['goal'],
            'current' => $prog['current'],
            'useText' => get_module_setting('usetext'),
            'simbolPosition' => get_module_setting('simbolPosition'),
            'simbol' => get_module_setting('simbol'),
            'useBar' => get_module_setting('usebar'),
            'showDollars' => get_module_setting('showdollars'),
        ];

        $html['paypal'] = $html['paypal'] ?? '';
        $html['paypal'] .= \LotgdTheme::renderModuleTemplate('funddrive/dohook/everyfooter.twig', $params);
    }
    elseif ('donation' == $hookname)
    {
        //-- Invalidate data cache when make a donation (ONLY)
        invalidatedatacache('mod_funddrive', true);
    }

    return $args;
}
