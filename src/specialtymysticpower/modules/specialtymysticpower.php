<?php

//addnews ready
// mail ready
// translator ready

function specialtymysticpower_getmoduleinfo()
{
    return [
        'name'     => 'Specialty - Mystical Powers',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '3.0.0',
        'download' => 'core_module',
        'category' => 'Specialties',
        'prefs'    => [
            'Specialty - Mystical Powers User Prefs,title',
            'skill' => 'Skill points in Mystical Powers,int|0',
            'uses'  => 'Uses of Mystical Powers allowed,int|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function specialtymysticpower_install()
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

function specialtymysticpower_uninstall()
{
    // Reset the specialty of anyone who had this specialty so they get to
    // rechoose at new day
    try
    {
        $charactersRepository = \Doctrine::getRepository('LotgdCore:Characters');
        $query                = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Characters', 'u')
            ->set('u.specialty', '')
            ->where('u.specialty = :specialty')

            ->setParameter('specialty', 'MP')

            ->getQuery()
            ->execute()
        ;
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);

        return false;
    }

    return true;
}

function specialtymysticpower_dohook($hookname, $args)
{
    global $session,$resline;

    $spec  = 'MP';
    $name  = \LotgdTranslator::t('specialty.name', [], 'module_specialtymysticpower');
    $ccode = '`%';

    switch ($hookname)
    {
        case 'dragonkill':
            set_module_pref('uses', 0);
            set_module_pref('skill', 0);
        break;
        case 'choose-specialty':
            if ('' == $session['user']['specialty'] || '0' == $session['user']['specialty'])
            {
                \LotgdNavigation::addHeader('category.basic');
                \LotgdNavigation::addNavNotl("{$ccode}{$name}`0", "newday.php?setspecialty={$spec}{$resline}");

                $params = [
                    'colorCode' => $ccode,
                    'spec'      => $spec,
                    'resLine'   => $resline,
                ];

                \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/specialtymysticpower/dohook/choose-specialty.twig', $params));
            }
        break;
        case 'set-specialty':
            if ($session['user']['specialty'] == $spec)
            {
                \LotgdResponse::pageStart($name);

                \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/specialtymysticpower/dohook/set-specialty.twig', []));
            }
        break;
        case 'specialtycolor':
            $args[$spec] = $ccode;
        break;
        case 'specialtynames':
            $args[$spec] = $name;
        break;
        case 'specialtymodules':
            $args[$spec] = 'specialtymysticpower';
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
                    'module_specialtymysticpower',
                ];
                $x = $new % 3;

                if (0 == $x)
                {
                    $lotgdBattleContent['battleend'][] = [
                        'battle.increment.specialty.gain',
                        [],
                        'module_specialtymysticpower',
                    ];
                    set_module_pref('uses', get_module_pref('uses') + 1);
                }
                else
                {
                    $lotgdBattleContent['battleend'][] = [
                        'battle.increment.specialty.need',
                        ['level' => \floor(3 - $x)],
                        'module_specialtymysticpower',
                    ];
                }
            }
        break;
        case 'newday':
            $bonus = LotgdSetting::getSetting('specialtybonus', 1);

            if ($session['user']['specialty'] == $spec)
            {
                $args['includeTemplatesPost']['@module/specialtymysticpower/dohook/newday.twig'] = [
                    'colorCode' => $ccode,
                    'spec'      => $spec,
                    'bonus'     => $bonus,
                ];
            }
            $amt = (int) (get_module_pref('skill') / 3);

            if ($session['user']['specialty'] == $spec)
            {
                $amt = $amt + $bonus;
            }
            set_module_pref('uses', $amt);
        break;
        case 'fightnav-specialties':
            $uses   = get_module_pref('uses');
            $script = $args['script'];

            //-- Change text domain for navigation
            \LotgdNavigation::setTextDomain('module_specialtymysticpower');

            if ($uses > 0)
            {
                \LotgdNavigation::addHeader('navigation.category.uses', [
                    'params' => [
                        'color' => $ccode,
                        'name'  => $name,
                        'uses'  => $uses,
                    ],
                ]);
                \LotgdNavigation::addNav('navigation.nav.skill1', "{$script}op=fight&skill={$spec}&l=1", [
                    'params' => ['color' => $ccode, 'use' => 1],
                ]);
            }

            if ($uses > 1)
            {
                \LotgdNavigation::addNav('navigation.nav.skill2', "{$script}op=fight&skill={$spec}&l=1", [
                    'params' => ['color' => $ccode, 'use' => 2],
                ]);
            }

            if ($uses > 2)
            {
                \LotgdNavigation::addNav('navigation.nav.skill3', "{$script}op=fight&skill={$spec}&l=1", [
                    'params' => ['color' => $ccode, 'use' => 3],
                ]);
            }

            if ($uses > 4)
            {
                \LotgdNavigation::addNav('navigation.nav.skill4', "{$script}op=fight&skill={$spec}&l=1", [
                    'params' => ['color' => $ccode, 'use' => 5],
                ]);
            }

            //-- Restore text domain for navigation
            \LotgdNavigation::setTextDomain();
        break;
        case 'apply-specialties':
            $skill = \LotgdRequest::getQuery('skill');
            $l     = \LotgdRequest::getQuery('l');

            if ($skill == $spec)
            {
                if (get_module_pref('uses') >= $l)
                {
                    switch ($l)
                    {
                        case 1:
                            LotgdKernel::get('lotgd_core.combat.buffs')->applyBuff('mp1', [
                                'startmsg'       => LotgdTranslator::t('skill.mp1.startmsg', [], 'module_specialtymysticpower'),
                                'name'           => LotgdTranslator::t('skill.mp1.name', [], 'module_specialtymysticpower'),
                                'rounds'         => 5,
                                'wearoff'        => LotgdTranslator::t('skill.mp1.wearoff', [], 'module_specialtymysticpower'),
                                'regen'          => $session['user']['level'],
                                'effectmsg'      => LotgdTranslator::t('skill.mp1.effectmsg', [], 'module_specialtymysticpower'),
                                'effectnodmgmsg' => LotgdTranslator::t('skill.mp1.effectnodmgmsg', [], 'module_specialtymysticpower'),
                                'aura'           => true,
                                'auramsg'        => LotgdTranslator::t('skill.mp1.auramsg', [], 'module_specialtymysticpower'),
                                'schema'         => 'module_specialtymysticpower',
                            ]);
                            break;
                        case 2:
                            LotgdKernel::get('lotgd_core.combat.buffs')->applyBuff('mp2', [
                                'startmsg'        => \LotgdTranslator::t('skill.mp2.startmsg', [], 'module_specialtymysticpower'),
                                'name'            => \LotgdTranslator::t('skill.mp2.name', [], 'module_specialtymysticpower'),
                                'rounds'          => 5,
                                'wearoff'         => \LotgdTranslator::t('skill.mp2.wearoff', [], 'module_specialtymysticpower'),
                                'minioncount'     => 1,
                                'effectmsg'       => LotgdTranslator::t('skill.mp2.effectmsg', [], 'module_specialtymysticpower'),
                                'minbadguydamage' => 1,
                                'maxbadguydamage' => $session['user']['level'] * 3,
                                'areadamage'      => true,
                                'schema'          => 'module_specialtymysticpower',
                            ]);
                            break;
                        case 3:
                            LotgdKernel::get('lotgd_core.combat.buffs')->applyBuff('mp3', [
                                'startmsg'       => \LotgdTranslator::t('skill.mp3.startmsg', [], 'module_specialtymysticpower'),
                                'name'           => \LotgdTranslator::t('skill.mp3.name', [], 'module_specialtymysticpower'),
                                'rounds'         => 5,
                                'wearoff'        => \LotgdTranslator::t('skill.mp3.wearoff', [], 'module_specialtymysticpower'),
                                'lifetap'        => 1, //ratio of damage healed to damage dealt
                                'effectmsg'      => \LotgdTranslator::t('skill.mp3.effectmsg', [], 'module_specialtymysticpower'),
                                'effectnodmgmsg' => \LotgdTranslator::t('skill.mp3.effectnodmgmsg', [], 'module_specialtymysticpower'),
                                'effectfailmsg'  => \LotgdTranslator::t('skill.mp3.effectfailmsg', [], 'module_specialtymysticpower'),
                                'schema'         => 'module_specialtymysticpower',
                            ]);
                            break;
                        case 5:
                            LotgdKernel::get('lotgd_core.combat.buffs')->applyBuff('mp5', [
                                'startmsg'       => \LotgdTranslator::t('skill.mp5.startmsg', [], 'module_specialtymysticpower'),
                                'name'           => \LotgdTranslator::t('skill.mp5.name', [], 'module_specialtymysticpower'),
                                'rounds'         => 5,
                                'wearoff'        => \LotgdTranslator::t('skill.mp5.wearoff', [], 'module_specialtymysticpower'),
                                'damageshield'   => 2, // ratio of damage reflected to damage received
                                'effectmsg'      => \LotgdTranslator::t('skill.mp5.effectmsg', [], 'module_specialtymysticpower'),
                                'effectnodmgmsg' => \LotgdTranslator::t('skill.mp5.effectnodmgmsg', [], 'module_specialtymysticpower'),
                                'effectfailmsg'  => \LotgdTranslator::t('skill.mp5.effectfailmsg', [], 'module_specialtymysticpower'),
                                'schema'         => 'module_specialtymysticpower',
                            ]);
                        break;
                    }

                    set_module_pref('uses', get_module_pref('uses') - $l);
                }
                else
                {
                    LotgdKernel::get('lotgd_core.combat.buffs')->applyBuff('mp0', [
                        'startmsg' => \LotgdTranslator::t('skill.mp0.startmsg', [], 'module_specialtymysticpower'),
                        'rounds'   => 1,
                        'schema'   => 'module_specialtymysticpower',
                    ]);
                }
            }
        break;
    }

    return $args;
}

function specialtymysticpower_run()
{
}
