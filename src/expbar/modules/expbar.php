<?php

// addnews ready
// mail ready
// translator ready

/**
 * Experience Bar.
 *
 * Descripción: Displays a bar that indicates when you have enough experience to be able to level up.
 *
 * Versiones:
 * - 1.0.0: Creación del módulo
 * - 1.1.0: Se han ajustado los parametros y la animación sólo se aplica al icono, se ha quitado la animación del progreso, cuando no se tiene el 100% de la exp
 * - 1.1.1: Se agrega la condición para que se active el hook
 * - 1.2.0: Adaptación a la versión 2.0.0 del núcleo
 * - 1.2.1: Se corrige error con el valor máximo para calcular el porcentaje
 * - 1.2.2: Se permite al usuario seleccionar si se muestra o no la experiencia necesaria y la actual
 * - 2.0.0: Public released and refactoring for 4.0.0 of Core game
 * - 3.0.0: Refactoring for 7.0.0 of Core game
 */
function expbar_getmoduleinfo()
{
    return [
        'name'     => 'Experience Bar',
        'version'  => '3.0.0',
        'author'   => '`%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a> - based on idea of JT Traub (core_module)',
        'category' => 'Stat Display',
        'download' => 'https://github.com/idmarinas/lotgd-modules',
        'prefs'    => [
            'Experience Bar,title',
            'user_showexpnumber' => 'Show current experience number,bool|0',
            'user_shownextgoal'  => 'Show the exp needed for next level (only if current exp is shown),bool|0',
        ],
        'requires' => [
            'lotgd' => '>=7.0.0|Need a version equal or greater than 7.0.0 IDMarinas Edition',
        ],
    ];
}

function expbar_install()
{
    module_addhook('charstats', false, 'return (bool) ($session["user"]["alive"]);');

    return true;
}

function expbar_uninstall()
{
    return true;
}

function expbar_dohook($hookname, $args)
{
    global $session;

    if ($hookname === 'charstats')
    {
        $params = [
            'text_domain'        => 'module_expbar',
            'experience_require' => LotgdTool::expForNextLevel($session['user']['level'], $session['user']['dragonkills']),
            'experience_current' => $session['user']['experience'],
            'level'             => $session['user']['level'],
            'dragonkills'       => $session['user']['dragonkills'],
            'show_num'           => get_module_pref('user_showexpnumber'),
            'show_next'          => get_module_pref('user_shownextgoal'),
        ];

        $pct = \round($params['experience_current'] / $params['experience_require'] * 100, 0);

        $params['can_level_up'] = ($params['experience_current'] >= $params['experience_require']);
        $params['show_label']  = ($params['show_num'] && $params['show_next']);
        $params['exp_percent'] = \max($pct, 0, \min($pct, 100));

        LotgdKernel::get("Lotgd\Core\Character\Stats")->setcharstat(
            LotgdTranslator::t('statistic.category.character.info', [], 'app_default'),
            LotgdTranslator::t('charstats.stat.experience', [], $params['text_domain']),
            LotgdTheme::render('@module/expbar_charstats_bar.twig', $params)
        );
    }

    return $args;
}

function expbar_run()
{
}
