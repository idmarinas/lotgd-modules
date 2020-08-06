<?php

function funddriverewards_getmoduleinfo()
{
    return [
        'name' => 'Fund Drive Rewards',
        'version' => '2.0.0',
        'author' => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Administrative',
        'download' => 'core_module',
        'settings' => [
            'Extra Forest Fights,title',
            'giveff' => 'Give extra forest fights?,bool|1',
            'ffstartat' => 'Starting at what percent of objective?,int|100',
            'ffperpct' => 'Give 1 fight per how many percent over start point?,int|10',
            'maxff' => 'Give no more than how many forest fights?,int|10',

            'Reduced Healing Cost,title',
            'giveheal' => 'Give reduced healing cost?,bool|1',
            'healstartat' => 'Starting at what percent of objective?,int|100',
            'healperpct' => 'Percent to reduce cost per percent over start point?,int|1',
            'maxheal' => 'Max percent to reduce healing cost?,int|10',
        ],
        'requires' => [
            'funddrive' => '2.0.0|Fund Drive Indicator.'
        ],
    ];
}

function funddriverewards_install()
{
    module_addhook('newday');
    module_addhook('healmultiply');

    return true;
}

function funddriverewards_uninstall()
{
    return true;
}

function funddriverewards_dohook($hookname, $args)
{
    global $session;

    require_once 'modules/funddrive/lib.php';

    $result = funddrive_getpercent();
    $percent = $result['percent'];
    $textDomain = 'module-funddriverewards';

    switch ($hookname)
    {
        case 'newday':
            //Do forest fights.
            if (get_module_setting('giveff'))
            {
                $params = [
                    'textDomain' => $textDomain,
                    'addedFights' => 0,
                    'staminaSystem' => is_module_active('staminasystem'),
                    'ffPerPct' => get_module_setting('ffperpct'),
                    'ffStartAt' => get_module_setting('ffstartat'),
                    'maxFf' => get_module_setting('maxff')
                ];

                if ($percent >= $params['ffStartAt'])
                {
                    $above = $percent - $params['ffStartAt'];
                    $params['addedFights'] = ceil($above / $params['ffPerPct']);
                    $params['addedFights'] = (int) min($params['addedFights'], $params['maxFf']);
                    $params['addedFights'] = max($params['addedFights'], 0);

                    if ($params['addedFights'] > 0)
                    {
                        if (is_module_active('staminasystem'))
                        {
                            require_once 'modules/staminasystem/lib/lib.php';

                            $stamina = $params['addedFights'] * 25000;
                            $args['turnstoday'] .= ", funddriverewards: Stamina {$stamina}";

                            addstamina($stamina);
                        }
                        else
                        {
                            $session['user']['turns'] += $params['addedFights'];
                            $args['turnstoday'] .= ", funddriverewards: Turns {$params['addedFights']}";
                        }
                    }
                }

                $args['includeTemplatesPost']['module/funddriverewards/dohook/newday.twig'] = $params;
            }
        break;
        case 'healmultiply':
            if (get_module_setting('giveheal'))
            {
                $params = [
                    'healPerPct' => get_module_setting('healperpct'),
                    'healStartAt' => get_module_setting('healstartat'),
                    'maxHeal' => get_module_setting('maxheal')
                ];

                if ((float) $params['healPerPct'] >= 1)
                {
                    $message = \LotgdTranslator::t('flash.message.heal.more', $params, $textDomain);
                }
                else
                {
                    $divider = funddriverewards_gcf(100, round($params['healPerPct'] * 100));
                    $params['healPerPct'] = $params['healPerPct'] * $divider;
                    $message = \LotgdTranslator::t('flash.message.heal.less', $params, $textDomain);
                }
                $pctoff = 0;

                if ($percent > $params['healStartAt'])
                {
                    $above = $percent - $params['healStartAt'];
                    $pctoff = round($above * $params['healPerPct'], 2);
                    $pctoff = min($args['maxHeal'], $pctoff);
                }

                $params['pctOff'] = $pctoff;

                $message .= '<br>'.\LotgdTranslator::t('flash.message.heal.result', $params, $textDomain);

                \LotgdFlashMessages::addInfoMessage($message);

                $args['alterpct'] *= ((100 - $pctoff) / 100);
            }
        break;
    }

    return $args;
}
function funddriverewards_gcf($a, $b)
{
    //efficient gcf detector for numbers with factors up to 97.
    $primes = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97];
    $return = 1;

    for ($i = 0; $i < count($primes); $i++)
    {
        if (0 == $a % $primes[$i] && 0 == $b % $primes[$i])
        {
            $return *= $primes[$i];
        }
    }

    return $return;
}
