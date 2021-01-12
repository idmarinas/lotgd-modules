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
 */
function expbar_getmoduleinfo()
{
    return [
        'name'     => 'Experience Bar',
        'version'  => '2.1.0',
        'author'   => '`%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a> - based on idea of JT Traub (core_module)',
        'category' => 'Stat Display',
        'download' => 'https://github.com/idmarinas/lotgd-modules',
        'prefs'    => [
            'Experience Bar,title',
            'user_showexpnumber' => 'Show current experience number,bool|0',
            'user_shownextgoal'  => 'Show the exp needed for next level (only if current exp is shown),bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
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

    switch ($hookname)
    {
        case 'charstats':
            require_once 'lib/experience.php';

            $params = [
                'textDomain'        => 'module_expbar',
                'experienceRequire' => exp_for_next_level($session['user']['level'], $session['user']['dragonkills']),
                'experienceCurrent' => $session['user']['experience'],
                'level'             => $session['user']['level'],
                'dragonkills'       => $session['user']['dragonkills'],
                'showNum'           => get_module_pref('user_showexpnumber'),
                'showNext'          => get_module_pref('user_shownextgoal'),
            ];

            $params['canLevelUp'] = ($params['experienceCurrent'] >= $params['experienceRequire']);
            $params['showLabel']  = ($params['showNum'] && $params['showNext']);

            \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/expbar_charstats_script.twig', $params));
            $bar = \LotgdTheme::render('@module/expbar_charstats_bar.twig', $params);

            setcharstat(
                \LotgdTranslator::t('statistic.category.character.info', [], 'app_default'),
                \LotgdTranslator::t('charstats.stat.experience', [], $params['textDomain']),
                $bar
            );
        break;
    }

    return $args;
}

function expbar_run()
{
}
