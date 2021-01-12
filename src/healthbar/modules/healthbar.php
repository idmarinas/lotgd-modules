<?php

// addnews ready
// mail ready
// translator ready

/**
 * Health Bar.
 *
 * Descripción: Displays a bar indicating the character's current health.
 *
 * Versiones:
 * - 1.0.0: Creación del módulo
 * - 1.1.0: Se agrega la animación y un icono.
 * - 1.1.1: La animación sólo se aplica al icono.
 * - 1.2.0: Adaptación a la versión 2.0.0 del núcleo
 * - 2.0.0: Public released and refactoring for 4.0.0 of Core game
 */
function healthbar_getmoduleinfo()
{
    return [
        'name'     => 'Health Bar',
        'version'  => '2.1.0',
        'author'   => '`%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a> - based on idea of JT Traub (core_module)',
        'category' => 'Stat Display',
        'download' => 'https://github.com/idmarinas/lotgd-modules',
        'prefs'    => [
            'Health Bar,title',
            'user_showcurrent' => 'Show health level as a number,bool|0',
            'user_showmax'     => 'Show max health (only if current),bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function healthbar_install()
{
    module_addhook('charstats');

    return true;
}

function healthbar_uninstall()
{
    return true;
}

function healthbar_dohook($hookname, $args)
{
    global $session;

    if ('charstats' == $hookname)
    {
        $params = [
            'textDomain'    => 'module_healthbar',
            'healthCurrent' => $session['user']['hitpoints'],
            'healthRealMax' => $session['user']['maxhitpoints'],
            'showCurrent'   => get_module_pref('user_showcurrent'),
            'showMax'       => get_module_pref('user_showmax'),
        ];

        if ( ! $session['user']['alive'])
        {
            $params['healthCurrent'] = $session['user']['soulpoints'];
            $params['healthRealMax'] = $session['user']['level'] * 10 + 50 + $session['user']['dragonkills'] * 2;
        }

        $params['healthMax'] = \max($params['healthCurrent'], $params['healthRealMax']);

        $pct                     = \round($params['healthCurrent'] / $params['healthMax'] * 100, 0);
        $params['healthPercent'] = \max($pct, 0, \min($pct, 100));

        $params['showLabel'] = (bool) ($params['showCurrent'] && $params['showMax']) || (bool) ($params['showCurrent']);

        $bar = \LotgdTheme::renderModuleTemplate('healthbar/dohook/charstats/bar.twig', $params);

        setcharstat(
            \LotgdTranslator::t('statistic.category.character.info', [], 'app_default'),
            \LotgdTranslator::t('charstats.stat.'.($session['user']['alive'] ? 'live' : 'death'), [], $params['textDomain']),
            $bar
        );
    }

    return $args;
}

function healthbar_run()
{
}
