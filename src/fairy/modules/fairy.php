<?php

function fairy_getmoduleinfo()
{
    return [
        'name'     => 'Forest Fairy',
        'version'  => '3.0.0',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'settings' => [
            'Fairy Forest Event Settings,title',
            'carrydk'   => 'Do max hitpoints gained carry across DKs?,bool|1',
            'hptoaward' => 'How many HP are given by the fairy?,range,1,5,1|1',
            'fftoaward' => 'How many FFs are given by the fairy?,range,1,5,1|1',
        ],
        'prefs' => [
            'Fairy Forest Event User Preferences,title',
            'extrahps' => 'How many extra hitpoints has the user gained?,int',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function fairy_install()
{
    module_addeventhook('forest', 'return 80;');
    module_addhook('hprecalc');

    return true;
}

function fairy_uninstall()
{
    return true;
}

function fairy_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'hprecalc':
            $args['total'] -= get_module_pref('extrahps');

            if ( ! get_module_setting('carrydk'))
            {
                $extra = get_module_pref('extrahps');

                $session['user']['permahitpoints'] -= $extra;
                $args['extra']                     -= $extra;
                set_module_pref('extrahps', 0);
            }
        break;
    }

    return $args;
}

function fairy_runevent($type)
{
    global $session;

    require_once 'lib/increment_specialty.php';

    // We assume this event only shows up in the forest currently.
    $from                          = 'forest.php?';
    $session['user']['specialinc'] = 'module:fairy';
    $textDomain                    = 'module_fairy';

    $op = \LotgdRequest::getQuery('op');

    $params = [
        'textDomain' => $textDomain,
    ];

    \LotgdNavigation::setTextDomain($textDomain);

    if ('' == $op || 'search' == $op)
    {
        $params['tpl'] = 'default';

        \LotgdNavigation::addNav('navigation.nav.give.yes', "{$from}op=give");
        \LotgdNavigation::addNav('navigation.nav.give.no', "{$from}op=dont");
    }
    elseif ('give' == $op)
    {
        $session['user']['specialinc'] = '';

        $params['tpl']           = 'give';
        $params['gived']         = false;
        $params['staminaSystem'] = is_module_active('staminasystem');

        if ($session['user']['gems'] > 0)
        {
            $params['gived'] = true;

            --$session['user']['gems'];
            \LotgdLog::debug('gave 1 gem to a fairy');

            switch (\mt_rand(1, 7))
            {
                case 1:
                    //## Added Stamina system support
                    $extra           = get_module_setting('fftoaward');
                    $params['turns'] = $extra;

                    if (is_module_active('staminasystem'))
                    {
                        $params['case'] = 1;
                        require_once 'modules/staminasystem/lib/lib.php';

                        $stamina = $extra * 25000;
                        addstamina($stamina);
                        \LotgdLog::debug('gained stamina for fairy in forest');
                    }
                    else
                    {
                        $params['case'] = 2;

                        \LotgdLog::debug('gained turns for fairy in forest');
                        $session['user']['turns'] += $extra;
                    }
                break;
                case 2:
                case 3:
                    $params['case'] = 3;
                    $session['user']['gems'] += 2;
                    \LotgdLog::debug('found 2 gem from a fairy');
                break;
                case 4:
                case 5:
                    $params['case']      = 4;
                    $params['permanent'] = 0;
                    $params['extra']     = get_module_setting('hptoaward');

                    //-- Added IDMarinas version support >= 0.7.0
                    if (get_module_setting('carrydk') && ( ! is_module_active('globalhp') || get_module_setting('carrydk', 'globalhp')))
                    {
                        $params['permanent'] = 1;
                        $session['user']['permahitpoints'] += $extra;
                    }

                    $session['user']['maxhitpoints'] += $extra;
                    $session['user']['hitpoints']    += $extra;
                    set_module_pref('extrahps', get_module_pref('extrahps') + $extra);
                break;
                case 6:
                case 7:
                    $params['case'] = 5;
                    LotgdKernel::get('lotgd_core.tool.player_functions')->incrementSpecialty('`^');
                break;
            }
        }
        else
        {
            --$session['user']['turns'];
        }
    }
    else
    {
        $params['tpl'] = 'swat';

        $session['user']['specialinc'] = '';
    }

    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(LotgdTheme::render('@module/fairy/runevent.twig', $params));
}

function fairy_run()
{
}
