<?php

//addnews ready
// mail ready
// translator ready

function specialtythiefskills_getmoduleinfo()
{
    return [
        'name'     => 'Specialty - Thieving Skills',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '2.0.0',
        'download' => 'core_module',
        'category' => 'Specialties',
        'prefs'    => [
            'Specialty - Thieving Skills User Prefs,title',
            'skill' => 'Skill points in Thieving Skills,int|0',
            'uses'  => 'Uses of Thieving Skills allowed,int|0',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
        ],
    ];
}

function specialtythiefskills_install()
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

function specialtythiefskills_uninstall()
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

            ->setParameter('specialty', 'TS')

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

function specialtythiefskills_dohook($hookname, $args)
{
    global $session,$resline;

    $spec  = 'TS';
    $name  = \LotgdTranslator::t('specialty.name', [], 'module-specialtythiefskills');
    $ccode = '`^';

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

                \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('specialtythiefskills/dohook/choose-specialty.twig', $params));
            }
        break;
        case 'set-specialty':
            if ($session['user']['specialty'] == $spec)
            {
                \LotgdResponse::pageStart($name);

                \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('specialtythiefskills/dohook/set-specialty.twig', []));
            }
        break;
        case 'specialtycolor':
            $args[$spec] = $ccode;
        break;
        case 'specialtynames':
            $args[$spec] = $name;
            break;
        case 'specialtymodules':
            $args[$spec] = 'specialtythiefskills';
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
                    'module-specialtythiefskills',
                ];
                $x = $new % 3;

                if (0 == $x)
                {
                    $lotgdBattleContent['battleend'][] = [
                        'battle.increment.specialty.gain',
                        [],
                        'module-specialtythiefskills',
                    ];
                    set_module_pref('uses', get_module_pref('uses') + 1);
                }
                else
                {
                    $lotgdBattleContent['battleend'][] = [
                        'battle.increment.specialty.need',
                        ['level' => \floor(3 - $x)],
                        'module-specialtythiefskills',
                    ];
                }
            }
        break;
        case 'newday':
            $bonus = getsetting('specialtybonus', 1);

            if ($session['user']['specialty'] == $spec)
            {
                $args['includeTemplatesPost']['module/specialtythiefskills/dohook/newday.twig'] = [
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
            \LotgdNavigation::setTextDomain('module-specialtythiefskills');

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
                            apply_buff('ts1', [
                                'startmsg'     => \LotgdTranslator::t('skill.ts1.startmsg', [], 'module-specialtythiefskills'),
                                'name'         => \LotgdTranslator::t('skill.ts1.name', [], 'module-specialtythiefskills'),
                                'rounds'       => 5,
                                'wearoff'      => \LotgdTranslator::t('skill.ts1.wearoff', [], 'module-specialtythiefskills'),
                                'roundmsg'     => \LotgdTranslator::t('skill.ts1.roundmsg', [], 'module-specialtythiefskills'),
                                'badguyatkmod' => 0.5,
                                'schema'       => 'module-specialtythiefskills',
                            ]);
                            break;
                        case 2:
                            apply_buff('ts2', [
                                'startmsg' => \LotgdTranslator::t('skill.ts2.startmsg', [], 'module-specialtythiefskills'),
                                'name'     => \LotgdTranslator::t('skill.ts2.name', [], 'module-specialtythiefskills'),
                                'rounds'   => 5,
                                'wearoff'  => \LotgdTranslator::t('skill.ts2.waroff', [], 'module-specialtythiefskills'),
                                'atkmod'   => 2,
                                'roundmsg' => \LotgdTranslator::t('skill.ts2.roundmsg', [], 'module-specialtythiefskills'),
                                'schema'   => 'module-specialtythiefskills',
                            ]);
                            break;
                        case 3:
                            apply_buff('ts3', [
                                'startmsg'     => \LotgdTranslator::t('skill.ts3.startmsg', [], 'module-specialtythiefskills'),
                                'name'         => \LotgdTranslator::t('skill.ts3.name', [], 'module-specialtythiefskills'),
                                'rounds'       => 5,
                                'wearoff'      => \LotgdTranslator::t('skill.ts3.wearoff', [], 'module-specialtythiefskills'),
                                'roundmsg'     => \LotgdTranslator::t('skill.ts3.roundmsg', [], 'module-specialtythiefskills'),
                                'badguyatkmod' => 0,
                                'schema'       => 'module-specialtythiefskills',
                            ]);
                            break;
                        case 5:
                            apply_buff('ts5', [
                                'startmsg' => \LotgdTranslator::t('skill.ts5.startmsg', [], 'module-specialtythiefskills'),
                                'name'     => \LotgdTranslator::t('skill.ts5.name', [], 'module-specialtythiefskills'),
                                'rounds'   => 5,
                                'wearoff'  => \LotgdTranslator::t('skill.ts5.wearoff', [], 'module-specialtythiefskills'),
                                'atkmod'   => 3,
                                'defmod'   => 3,
                                'roundmsg' => \LotgdTranslator::t('skill.ts5.roundmsg', [], 'module-specialtythiefskills'),
                                'schema'   => 'module-specialtythiefskills',
                            ]);
                        break;
                    }

                    set_module_pref('uses', get_module_pref('uses') - $l);
                }
                else
                {
                    apply_buff('ts0', [
                        'startmsg' => \LotgdTranslator::t('skill.ts0.startmsg', [], 'module-specialtythiefskills'),
                        'rounds'   => 1,
                        'schema'   => 'module-specialtythiefskills',
                    ]);
                }
            }
        break;
    }

    return $args;
}

function specialtythiefskills_run()
{
}
