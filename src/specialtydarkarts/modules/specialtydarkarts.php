<?php

use Tracy\Debugger;
//addnews ready
// mail ready
// translator ready

function specialtydarkarts_getmoduleinfo()
{
    return [
        'name'     => 'Specialty - Dark Arts',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '3.0.0',
        'download' => 'core_module',
        'category' => 'Specialties',
        'prefs'    => [
            'Specialty - Dark Arts User Prefs,title',
            'skill' => 'Skill points in Dark Arts,int|0',
            'uses'  => 'Uses of Dark Arts allowed,int|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function specialtydarkarts_install()
{
    module_addhook('choose-specialty');
    module_addhook('set-specialty');
    module_addhook('fightnav-specialties');
    module_addhook('apply-specialties');
    module_addhook_priority('newday', 900);
    module_addhook('incrementspecialty');
    module_addhook('specialtynames');
    module_addhook('specialtymodules');
    module_addhook('specialtycolor');
    module_addhook('dragonkill');

    return true;
}

function specialtydarkarts_uninstall()
{
    try
    {
        $charactersRepository = Doctrine::getRepository('LotgdCore:Avatar');
        $query                = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Avatar', 'u')
            ->set('u.specialty', '')
            ->where('u.specialty = :specialty')

            ->setParameter('specialty', 'DA')

            ->getQuery()
            ->execute()
        ;
    }
    catch (Throwable $th)
    {
        Debugger::log($th);

        return false;
    }

    return true;
}

function specialtydarkarts_dohook($hookname, $args)
{
    global $session, $resline;

    $spec  = 'DA';
    $name  = LotgdTranslator::t('specialty.name', [], 'module_specialtydarkarts');
    $ccode = '`$';

    switch ($hookname)
    {
        case 'dragonkill':
            set_module_pref('uses', 0);
            set_module_pref('skill', 0);
        break;
        case 'choose-specialty':
            if ('' == $session['user']['specialty'] || '0' == $session['user']['specialty'])
            {
                LotgdNavigation::addHeader('category.basic');
                LotgdNavigation::addNavNotl("{$ccode}{$name}`0", "newday.php?setspecialty={$spec}{$resline}");

                $params = [
                    'colorCode' => $ccode,
                    'spec'      => $spec,
                    'resLine'   => $resline,
                ];

                LotgdResponse::pageAddContent(LotgdTheme::render('@module/specialtydarkarts/dohook/choose-specialty.twig', $params));
            }
        break;
        case 'set-specialty':
            if ($session['user']['specialty'] == $spec)
            {
                LotgdResponse::pageStart($name);

                LotgdResponse::pageAddContent(LotgdTheme::render('@module/specialtydarkarts/dohook/set-specialty.twig', []));
            }
        break;
        case 'specialtycolor':
            $args[$spec] = $ccode;
        break;
        case 'specialtynames':
            $args[$spec] = $name;
        break;
        case 'specialtymodules':
            $args[$spec] = 'specialtydarkarts';
        break;
        case 'incrementspecialty':
            if ($session['user']['specialty'] == $spec)
            {
                global $lotgdBattleContent;

                $new = get_module_pref('skill') + 1;
                set_module_pref('skill', $new);
                $c = $args['color'];

                $lotgdBattleContent['battleend'][] = [
                    'battle.increment.specialty.level',
                    ['color' => $c, 'name' => $name, 'level' => $new],
                    'module_specialtydarkarts',
                ];
                $x = $new % 3;

                if (0 == $x)
                {
                    $lotgdBattleContent['battleend'][] = [
                        'battle.increment.specialty.gain',
                        [],
                        'module_specialtydarkarts',
                    ];
                    set_module_pref('uses', get_module_pref('uses') + 1);
                }
                else
                {
                    $lotgdBattleContent['battleend'][] = [
                        'battle.increment.specialty.need',
                        ['level' => \floor(3 - $x)],
                        'module_specialtydarkarts',
                    ];
                }
            }
        break;
        case 'newday':
            $bonus = (int) LotgdSetting::getSetting('specialtybonus', 1);

            if ($session['user']['specialty'] == $spec)
            {
                $args['includeTemplatesPost']['@module/specialtydarkarts/dohook/newday.twig'] = [
                    'colorCode' => $ccode,
                    'spec'      => $spec,
                    'bonus'     => $bonus,
                ];
            }
            $amt = (int) (get_module_pref('skill') / 3);

            if ($session['user']['specialty'] == $spec)
            {
                $amt += $bonus;
            }

            set_module_pref('uses', $amt);
        break;
        case 'fightnav-specialties':
            $uses   = get_module_pref('uses');
            $script = $args['script'];

            //-- Change text domain for navigation
            LotgdNavigation::setTextDomain('module_specialtydarkarts');

            if ($uses > 0)
            {
                LotgdNavigation::addHeader('navigation.category.uses', [
                    'params' => [
                        'color' => $ccode,
                        'name'  => $name,
                        'uses'  => $uses,
                    ],
                ]);
                LotgdNavigation::addNav('navigation.nav.skill1', "{$script}op=fight&skill={$spec}&l=1", [
                    'params' => ['color' => $ccode, 'use' => 1],
                ]);
            }

            if ($uses > 1)
            {
                LotgdNavigation::addNav('navigation.nav.skill2', "{$script}op=fight&skill={$spec}&l=2", [
                    'params' => ['color' => $ccode, 'use' => 2],
                ]);
            }

            if ($uses > 2)
            {
                LotgdNavigation::addNav('navigation.nav.skill3', "{$script}op=fight&skill={$spec}&l=3", [
                    'params' => ['color' => $ccode, 'use' => 3],
                ]);
            }

            if ($uses > 4)
            {
                LotgdNavigation::addNav('navigation.nav.skill4', "{$script}op=fight&skill={$spec}&l=5", [
                    'params' => ['color' => $ccode, 'use' => 5],
                ]);
            }

            //-- Restore text domain for navigation
            LotgdNavigation::setTextDomain();
        break;
        case 'apply-specialties':
            $skill = LotgdRequest::getQuery('skill');
            $l     = LotgdRequest::getQuery('l');

            if ($skill == $spec)
            {
                if (get_module_pref('uses') >= $l)
                {
                    switch ($l)
                    {
                        case 1:
                            if (LotgdSetting::getSetting('enablecompanions', true))
                            {
                                LotgdKernel::get('lotgd_core.combat.buffer')->applyCompanion('skeleton_warrior', [
                                    'name'         => LotgdTranslator::t('skill.companion.name', [], 'module_specialtydarkarts'),
                                    'hitpoints'    => \round($session['user']['level'] * 3.33, 0) + 10,
                                    'maxhitpoints' => \round($session['user']['level'] * 3.33, 0) + 10,
                                    'attack'       => \round((($session['user']['level'] / 4) + 2)) * \round((($session['user']['level'] / 3) + 2)) + 1.5,
                                    'defense'      => \floor((($session['user']['level'] / 3) + 0)) * \ceil(($session['user']['level'] / 6) + 2) + 2.5,
                                    'dyingtext'    => LotgdTranslator::t('skill.companion.dyingtext', [], 'module_specialtydarkarts'),
                                    'abilities'    => [
                                        'fight' => true,
                                    ],
                                    'ignorelimit' => true, // Does not count towards companion limit...
                                ], true);
                            // Because of this last "true" the companion can be added any time.
                                // Even, if the player controls already more companions than normally allowed!
                            }
                            else
                            {
                                LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('da1', [
                                    'startmsg'        => LotgdTranslator::t('skill.da1.startmsg', [], 'module_specialtydarkarts'),
                                    'name'            => LotgdTranslator::t('skill.da1.name', [], 'module_specialtydarkarts'),
                                    'rounds'          => 5,
                                    'wearoff'         => LotgdTranslator::t('skill.da1.wearoff', [], 'module_specialtydarkarts'),
                                    'minioncount'     => \round($session['user']['level'] / 3) + 1,
                                    'maxbadguydamage' => \round($session['user']['level'] / 2, 0) + 1,
                                    'effectmsg'       => LotgdTranslator::t('skill.da1.effectmsg', [], 'module_specialtydarkarts'),
                                    'effectnodmgmsg'  => LotgdTranslator::t('skill.da1.effectnodmgmsg', [], 'module_specialtydarkarts'),
                                    'schema'          => 'module_specialtydarkarts',
                                ]);
                            }
                        break;
                        case 2:
                            LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('da2', [
                                'startmsg'        => LotgdTranslator::t('skill.da2.startmsg', [], 'module_specialtydarkarts'),
                                'effectmsg'       => LotgdTranslator::t('skill.da2.effectmsg', [], 'module_specialtydarkarts'),
                                'rounds'          => 1,
                                'minioncount'     => 1,
                                'maxbadguydamage' => \round($session['user']['attack'] * 3, 0),
                                'minbadguydamage' => \round($session['user']['attack'] * 1.5, 0),
                                'schema'          => 'module_specialtydarkarts',
                            ]);
                            break;
                        case 3:
                            LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('da3', [
                                'startmsg'     => LotgdTranslator::t('skill.da3.startmsg', [], 'module_specialtydarkarts'),
                                'name'         => LotgdTranslator::t('skill.da3.name', [], 'module_specialtydarkarts'),
                                'rounds'       => 5,
                                'wearoff'      => LotgdTranslator::t('skill.da3.wearoff', [], 'module_specialtydarkarts'),
                                'badguydmgmod' => 0.5,
                                'roundmsg'     => LotgdTranslator::t('skill.da3.roundmsg', [], 'module_specialtydarkarts'),
                                'schema'       => 'module_specialtydarkarts',
                            ]);
                            break;
                        case 5:
                            LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('da5', [
                                'startmsg'     => LotgdTranslator::t('skill.da5.startmsg', [], 'module_specialtydarkarts'),
                                'name'         => LotgdTranslator::t('skill.da5.name', [], 'module_specialtydarkarts'),
                                'rounds'       => 5,
                                'wearoff'      => LotgdTranslator::t('skill.da5.wearoff', [], 'module_specialtydarkarts'),
                                'badguyatkmod' => 0,
                                'badguydefmod' => 0,
                                'roundmsg'     => LotgdTranslator::t('skill.da5.roundmsg', [], 'module_specialtydarkarts'),
                                'schema'       => 'module_specialtydarkarts',
                            ]);
                            break;
                    }

                    set_module_pref('uses', get_module_pref('uses') - $l);
                }
                else
                {
                    LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('da0', [
                        'startmsg' => LotgdTranslator::t('skill.da0.startmsg', [], 'module_specialtydarkarts'),
                        'rounds'   => 1,
                        'schema'   => 'module_specialtydarkarts',
                    ]);
                }
            }
        break;
    }

    return $args;
}

function specialtydarkarts_run()
{
}
