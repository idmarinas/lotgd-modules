<?php

function moons_getmoduleinfo()
{
    return [
        'name'     => 'Moons',
        'author'   => 'JT Traub, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '3.1.0',
        'category' => 'General',
        'download' => 'core_module',
        //-- Restore to old format
        'settings' => [
            'First Moon,title',
                'moon1' => 'Is the first moon active?,bool|1',
                'moon1cycle' => 'Days in the first moons lunar cycle,range,10,60,1|23',
                'moon1place' => 'Place in cycle?,range,1,60,1|',
            'Second Moon,title',
                'moon2' => 'Is the second moon active?,bool|0',
                'moon2cycle' => 'Days in the second moons lunar cycle,range,10,60,1|43',
                'moon2place' => 'Place in cycle?,range,1,60,1|',
            'Third Moon,title',
                'moon3' => 'Is the third moon active?,bool|0',
                'moon3cycle' => 'Days in the third moons lunar cycle,range,10,60,1|37',
                'moon3place' => 'Place in cycle?,range,1,60,1|',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

/**
 * Get phase of moon.
 *
 * @param int $cur
 * @param int $max
 */
function moons_phase($cur, $max): string
{
    // waning 1/4 to new
    $phase = 'moon.phase.crescent.waning';

    if ($cur < $max * .12)
    {
        // new to first quarter
        $phase = 'moon.phase.new';
    }
    elseif ($cur < $max * .25)
    {
        // first quarter to waxing half
        $phase = 'moon.phase.crescent.waxing';
    }
    elseif ($cur < $max * .37)
    {
        // waxing half to 3/4 full
        $phase = 'moon.phase.full.half';
    }
    elseif ($cur < $max * .5)
    {
        // 3/4 full to full
        $phase = 'moon.phase.gibbous.waxing';
    }
    elseif ($cur < $max * .62)
    {
        // full to waning 3/4
        $phase = 'moon.phase.full.full';
    }
    elseif ($cur < $max * .75)
    {
        // waning 3/4 to waning half
        $phase = 'moon.phase.gibbous.waning';
    }
    elseif ($cur < $max * .87)
    {
        // waning half to waning 1/4
        $phase = 'moon.phase.full.half.waning';
    }

    return $phase;
}

function moons_install()
{
    module_addhook('newday-runonce');
    module_addhook_priority('newday', 1100);
    module_addhook('village-desc');
    module_addhook('forest-desc');
    module_addhook('landsky-moons');
    module_addhook('journey-desc'); // for the worldmap

    if ( ! get_module_setting('moon1place', 'moons'))
    {
        $place = e_rand(1, (int) get_module_setting('moon1cycle', 'moons'));
        set_module_setting('moon1place', $place);
        $place = e_rand(1, (int) get_module_setting('moon2cycle', 'moons'));
        set_module_setting('moon2place', $place);
        $place = e_rand(1, (int) get_module_setting('moon3cycle', 'moons'));
        set_module_setting('moon3place', $place);
    }

    return true;
}

function moons_uninstall()
{
    return true;
}

function moons_dohook($hookname, $args)
{
    global $session;

    $moon1 = get_module_setting('moon1');
    $moon2 = get_module_setting('moon2');
    $moon3 = get_module_setting('moon3');

    if ( ! $moon1 && ! $moon2 && ! $moon3)
    {
        return $args;
    }

    $textDomain = 'module_moons';

    switch ($hookname)
    {
        case 'newday-runonce':
            $place1 = get_module_setting('moon1place', 'moons') + 1;
            $place2 = get_module_setting('moon2place', 'moons') + 1;
            $place3 = get_module_setting('moon3place', 'moons') + 1;

            //-- Restart if is more than 60
            $place1 = $place1 > 60 ? 1 : $place1;
            $place2 = $place2 > 60 ? 1 : $place2;
            $place3 = $place3 > 60 ? 1 : $place3;

            set_module_setting('moon1place', $place1, 'moons');
            set_module_setting('moon2place', $place2, 'moons');
            set_module_setting('moon3place', $place3, 'moons');

            modulehook('moon-cyclechange');
        break;
        case 'newday':
            $moon1Place = get_module_setting('moon1place');
            $moon1Cycle = get_module_setting('moon1cycle');

            $moon2Place = get_module_setting('moon2place');
            $moon2Cycle = get_module_setting('moon2cycle');

            $moon3Place = get_module_setting('moon3place');
            $moon3Cycle = get_module_setting('moon3cycle');

            $args['includeTemplatesPost']['@module/moons_newday.twig'] = [
                'textDomain' => $textDomain,
                'moon1'      => $moon1,
                'moon1Place' => $moon1Place,
                'moon1Cycle' => $moon1Cycle,
                'moon1Phase' => moons_phase($moon1Place, $moon1Cycle),
                'moon2'      => $moon2,
                'moon2Place' => $moon2Place,
                'moon2Cycle' => $moon2Cycle,
                'moon2Phase' => moons_phase($moon2Place, $moon2Cycle),
                'moon3'      => $moon3,
                'moon3Place' => $moon3Place,
                'moon3Cycle' => $moon3Cycle,
                'moon3Phase' => moons_phase($moon3Place, $moon3Cycle),
            ];
        break;
        case 'village-desc':
        case 'forest-desc':
        case 'journey-desc':
            // The dwarven town is underground, so let's handle that specially.
            // If we are inside the town and the dwarf modules is active AND
            // then, if this user is in the dwarf town, show no moons.
            if (is_module_active('landsky') || ('village-desc' == $hookname && is_module_active('racedwarf')
                && get_module_setting('villagename', 'racedwarf') == $session['user']['location']
            ))
            {
                break;
            }

            $moon1Place = get_module_setting('moon1place');
            $moon1Cycle = get_module_setting('moon1cycle');

            $moon2Place = get_module_setting('moon2place');
            $moon2Cycle = get_module_setting('moon2cycle');

            $moon3Place = get_module_setting('moon3place');
            $moon3Cycle = get_module_setting('moon3cycle');

            $moonsCount = 1;
            $moonsCount += $moon2 ? 1 : 0;
            $moonsCount += $moon3 ? 1 : 0;

            $prefix = 'prefix.forest';

            if ('village-desc' == $hookname)
            {
                $prefix = 'prefix.village';
            }

            $args[] = [
                $prefix,
                [
                    'count' => $moonsCount,
                ],
                $textDomain,
            ];

            $args[] = [
                'info',
                [
                    'phase'    => LotgdTranslator::t(moons_phase($moon1Place, $moon1Cycle), [], $textDomain),
                    'moonName' => LotgdTranslator::t('moon.name.01', [], $textDomain),
                ],
                $textDomain,
            ];

            if ($moon2)
            {
                $args[] = [
                    'info',
                    [
                        'phase'    => LotgdTranslator::t(moons_phase($moon2Place, $moon2Cycle), [], $textDomain),
                        'moonName' => LotgdTranslator::t('moon.name.02', [], $textDomain),
                    ],
                    $textDomain,
                ];
            }

            if ($moon3)
            {
                $args[] = [
                    'info',
                    [
                        'phase'    => LotgdTranslator::t(moons_phase($moon3Place, $moon3Cycle), [], $textDomain),
                        'moonName' => LotgdTranslator::t('moon.name.03', [], $textDomain),
                    ],
                    $textDomain,
                ];
            }
        break;
        case 'landsky-moons':
            $moon1Place = get_module_setting('moon1place');
            $moon1Cycle = get_module_setting('moon1cycle');

            $moon2Place = get_module_setting('moon2place');
            $moon2Cycle = get_module_setting('moon2cycle');

            $moon3Place = get_module_setting('moon3place');
            $moon3Cycle = get_module_setting('moon3cycle');

            $args[] = [
                'info',
                [
                    'phase'    => LotgdTranslator::t(moons_phase($moon1Place, $moon1Cycle), [], $textDomain),
                    'moonName' => LotgdTranslator::t('moon.name.01', [], $textDomain),
                ],
                $textDomain,
            ];

            if ($moon2)
            {
                $args[] = [
                    'info',
                    [
                        'phase'    => LotgdTranslator::t(moons_phase($moon2Place, $moon2Cycle), [], $textDomain),
                        'moonName' => LotgdTranslator::t('moon.name.02', [], $textDomain),
                    ],
                    $textDomain,
                ];
            }

            if ($moon3)
            {
                $args[] = [
                    'info',
                    [
                        'phase'    => LotgdTranslator::t(moons_phase($moon3Place, $moon3Cycle), [], $textDomain),
                        'moonName' => LotgdTranslator::t('moon.name.03', [], $textDomain),
                    ],
                    $textDomain,
                ];
            }
        break;
    }

    return $args;
}

function moons_run()
{
}
