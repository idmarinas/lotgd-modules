<?php

use Tracy\Debugger;
function clantrees_getmoduleinfo()
{
    return [
        'name'     => 'Clan Christmas Trees',
        'version'  => '2.0.0',
        'author'   => 'Sneakabout, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Clan',
        'download' => 'core_module',
        'settings' => [
            'Clan Christmas Trees Settings,title',
            'treebuy'     => 'Can you buy a tree?,bool|0',
            'treereward'  => 'How many turns do they get the buff for?,int|15',
            'besttree'    => 'Clan ID which the best tree?,viewonly',
            'competitive' => 'Is the tree decoration competitive?,bool|1',
            'treebonus'   => 'How many tree points do they get a buff at?,int|100',
            'salesman'    => 'Name of the Salesman?,|Sativ',
        ],
        'prefs' => [
            'gotbuff' => 'Has player gained the tree buff today?,bool|0',
        ],
        'prefs-clans' => [
            'Clan Christmas Trees Clan Preferences,title',
            'havetree'   => 'Does this clan have a tree yet?,bool|0',
            'treepoints' => 'How many points are in this tree?,int|0',
            'basetree'   => 'What size tree at start?,enum,0,None,10,Small,20,Medium,50,Grand|0',
            'time'       => 'How many turns have clan members put in?,int|0',
            'gold'       => 'How much gold spent on tinsel?,int|0',
            'gems'       => 'How many gems spent on baubles?,int|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function clantrees_install()
{
    module_addhook('village-desc');
    module_addhook('page-clan-tpl-params');
    module_addhook('newday');

    return true;
}

function clantrees_uninstall()
{
    return true;
}

function clantrees_decoratenav($havetree, $treebuy)
{
    LotgdNavigation::setTextDomain('module_clantrees');

    LotgdNavigation::addHeader('navigation.category.christmas');

    if ( ! $havetree && $treebuy)
    {
        LotgdNavigation::addNav('navigation.nav.buy.tree', 'runmodule.php?module=clantrees&op=buytree');
    }
    elseif ($havetree)
    {
        LotgdNavigation::addNav('navigation.nav.work', 'runmodule.php?module=clantrees&op=treetime');
        LotgdNavigation::addNav('navigation.nav.buy.baubles', 'runmodule.php?module=clantrees&op=treebaubles');
        LotgdNavigation::addNav('navigation.nav.buy.tinsel', 'runmodule.php?module=clantrees&op=treetinsel');
    }
    LotgdNavigation::addHeader('navigation.category.clan');

    LotgdNavigation::setTextDomain();
}

function clantrees_buff($turns)
{
    $gotbuff = get_module_pref('gotbuff');

    if ($gotbuff)
    {
        return;
    }

    LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('besttreespirit', [
        'name'     => LotgdTranslator::t('buff.name', [], 'module_clantrees'),
        'rounds'   => $turns,
        'wearoff'  => LotgdTranslator::t('buff.wearoff', [], 'module_clantrees'),
        'defmod'   => 1.15,
        'roundmsg' => LotgdTranslator::t('buff.roundmsg', [], 'module_clantrees'),
        'schema'   => 'module_clantrees',
    ]);

    set_module_pref('gotbuff', 1);
}

function clantrees_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'village-desc':
            if (get_module_setting('treebuy'))
            {
                $besttree = (int) get_module_setting('besttree');
                $params   = [
                    'salesman' => get_module_setting('salesman'),
                ];

                $args[] = ['section.hook.village.salesman', $params, 'module_clantrees'];

                if ($besttree !== 0)
                {
                    if (get_module_setting('competitive'))
                    {
                        $repository = Doctrine::getRepository('LotgdCore:Clans');
                        $query      = $repository->createQueryBuilder('u');

                        try
                        {
                            $params['clanName'] = $query->select('u.clanname')
                                ->where('u.clanid = :id')
                                ->setParameter('id', $besttree)

                                ->getQuery()
                                ->getSingleScalarResult()
                            ;

                            $args[] = ['section.hook.village.besttree', $params, 'module_clantrees'];
                        }
                        catch (Throwable $th)
                        {
                            Debugger::log($th);

                            set_module_setting('besttree', 0);
                        }
                    }
                    else
                    {
                        $args[] = ['section.hook.village.decoration', $params, 'module_clantrees'];
                    }
                }
            }
        break;
        case 'page-clan-tpl-params':
            if ( ! get_module_setting('treebuy'))
            {
                break;
            }
            $op     = LotgdRequest::getQuery('op');
            $clanid = $session['user']['clanid'];
            $detail = LotgdRequest::getQuery('detail');

            if ('' == $op && ( ! $detail || ($detail == $clanid)) && ($clanid && $session['user']['clanrank'] > CLAN_APPLICANT))
            {
                $treebuy    = get_module_setting('treebuy');
                $hastree    = get_module_objpref('clans', $clanid, 'havetree');
                $treepoints = get_module_objpref('clans', $clanid, 'treepoints');
                $besttree   = get_module_setting('besttree');
                $treereward = get_module_setting('treereward');
                $treebonus  = get_module_setting('treebonus');

                if ($hastree || $treebuy)
                {
                    clantrees_decoratenav($hastree, $treebuy);
                }

                if ( ! $hastree && $treebuy)
                {
                    $args['includeTemplatesPre']['@module/clantrees/dohook/notree.twig'] = [
                        'textDomain' => 'module_clantrees',
                        'leader'     => (CLAN_LEADER == $session['user']['clanrank']),
                        'salesman'   => get_module_setting('salesman'),
                    ];

                    break;
                }

                // We don't have a tree, so we cannot do anything else.
                if ( ! $hastree)
                {
                    break;
                }

                // Handle the bonus in just one place
                if ($treepoints >= $treebonus)
                {
                    clantrees_buff($treereward * ($besttree == $clanid ? 2 : 1));
                }

                $args['includeTemplatesPre']['@module/clantrees/dohook/tree.twig'] = [
                    'textDomain'  => 'module_clantrees',
                    'leader'      => (CLAN_LEADER == $session['user']['clanrank']),
                    'salesman'    => get_module_setting('salesman'),
                    'isBestTree'  => ($clanid == $besttree),
                    'bonus'       => ($treepoints >= $treebonus),
                    'competitive' => get_module_setting('competitive'),
                ];
            }
        break;
        case 'newday':
            set_module_pref('gotbuff', 0);
        break;
        default: break;
    }

    return $args;
}

function clantrees_runevent()
{
}

function clantrees_run()
{
    global $session;

    $op = LotgdRequest::getQuery('op');

    $gems   = $session['user']['gems'];
    $gold   = $session['user']['gold'];
    $clanid = $session['user']['clanid'];

    $textDomain = 'module_clantrees';

    $params = [
        'textDomain' => $textDomain,
        'salesman'   => get_module_setting('salesman'),
    ];

    LotgdResponse::pageStart('title', [], $textDomain);

    if ('buytree' == $op)
    {
        $params['tpl'] = 'buytree';

        LotgdNavigation::addNav('navigation.nav.leave', 'clan.php');

        if ($gold < 5000 || $gems < 5)
        {
            $params['notGold'] = true;
        }

        LotgdNavigation::addHeader('navigation.category.buy');

        if (($gold >= 5000) && ($gems >= 5))
        {
            LotgdNavigation::addNav('navigation.nav.buy.small', 'runmodule.php?module=clantrees&op=tree&size=small');
        }

        if (($gold >= 10000) && ($gems >= 10))
        {
            LotgdNavigation::addNav('navigation.nav.buy.normal', 'runmodule.php?module=clantrees&op=tree&size=normal');
        }

        if (($gold >= 25000) && ($gems >= 25))
        {
            LotgdNavigation::addNav('navigation.nav.buy.grand', 'runmodule.php?module=clantrees&op=tree&size=grand');
        }
    }
    elseif ('tree' == $op)
    {
        $size = LotgdRequest::getQuery('size');

        $params['tpl']  = 'tree';
        $params['size'] = $size;

        LotgdNavigation::addNav('navigation.nav.return', 'clan.php');

        if ('small' == $size)
        {
            $basetree = 10;
            $session['user']['gold'] -= 5000;
            $session['user']['gems'] -= 5;
        }
        elseif ('normal' == $size)
        {
            $basetree = 20;
            $session['user']['gold'] -= 10000;
            $session['user']['gems'] -= 10;
        }
        elseif ('grand' == $size)
        {
            $basetree = 50;
            $session['user']['gold'] -= 25000;
            $session['user']['gems'] -= 25;
        }

        set_module_objpref('clans', $clanid, 'basetree', $basetree);
        set_module_objpref('clans', $clanid, 'havetree', 1);
    }
    elseif ('treetime' == $op)
    {
        $params['tpl']           = 'treetime';
        $params['staminaSystem'] = is_module_active('staminasystem');

        LotgdNavigation::addNav('navigation.nav.return', 'clan.php');

        //-- Stamina system compatibility
        if ($params['staminaSystem'])
        {
            require_once 'modules/staminasystem/lib/lib.php';

            $stamina   = get_stamina(3, true);
            $block     = \min(\floor($stamina / 25000), 10);
            $replyinfo = [
                'replystuff' => LotgdTranslator::t('section.run.treetime.form.stamina', [], $textDomain).',range,0,'.$block.',1',
            ];
        }
        else
        {
            // Restrict the player only spending the turns they have!
            $replyinfo = [
                'replystuff' => LotgdTranslator::t('section.run.treetime.form.turn', [], $textDomain).',range,0,'.$session['user']['turns'].',1',
            ];
        }

        require_once 'lib/showform.php';

        $params['form'] = lotgd_showform($replyinfo, [], true, false, false);
    }
    elseif ('treetinsel' == $op)
    {
        $params['tpl'] = 'treetinsel';

        LotgdNavigation::addNav('navigation.nav.return', 'clan.php');

        $replyinfo = [
            'replystuff' => LotgdTranslator::t('section.run.treetinsel.form.gold', [], $textDomain).',int',
        ];

        require_once 'lib/showform.php';

        $params['form'] = lotgd_showform($replyinfo, [], true, false, false);
    }
    elseif ('treebaubles' == $op)
    {
        $params['tpl'] = 'treebaubles';

        LotgdNavigation::addNav('navigation.nav.return', 'clan.php');

        $replyinfo = [
            'replystuff' => LotgdTranslator::t('section.run.treebaubles.form.gems', [], $textDomain).'Gems to invest,int',
        ];
        require_once 'lib/showform.php';

        $params['form'] = lotgd_showform($replyinfo, [], true, false, false);
    }
    elseif ('alter' == $op)
    {
        $params['tpl']           = 'alter';
        $params['staminaSystem'] = is_module_active('staminasystem');

        $what    = LotgdRequest::getQuery('what');
        $howmuch = LotgdRequest::getPost('replystuff');

        $params['what'] = $what;

        $cur   = get_module_objpref('clans', $clanid, $what);
        $field = $what;
        $field = ('time' == $field) ? 'turns' : $field;

        LotgdNavigation::addNav('navigation.nav.return', 'clan.php');

        if ($params['staminaSystem'])
        {
            require_once 'modules/staminasystem/lib/lib.php';

            $stamina = get_stamina(3, true);
            $block   = \min(\floor($stamina / 25000), 10);
        }

        if (0 == $howmuch)
        {
            LotgdFlashMessages::addErrorMessage(LotgdTranslator::t('flash.message.zero.'.$what, [], $textDomain));
        }
        elseif (('turns' == $field && $params['staminaSystem'] && $block < $howmuch) || $session['user'][$field] < $howmuch)
        {
            LotgdFlashMessages::addErrorMessage(LotgdTranslator::t('flash.message.none.'.$what, [], $textDomain));
        }
        else
        {
            //-- Everything works
            set_module_objpref('clans', $clanid, $what, $cur + $howmuch);

            if ('turns' == $field && $params['staminaSystem'])
            {
                removestamina($howmuch * 25000);
            }
            else
            {
                $session['user'][$field] -= $howmuch;
            }

            // Recalculate the tree since it changed.
            $points = get_module_objpref('clans', $clanid, 'basetree');
            $points += get_module_objpref('clans', $clanid, 'gems');
            $points += \floor(get_module_objpref('clans', $clanid, 'time') / 10);
            $points += \floor(\sqrt(get_module_objpref('clans', $clanid, 'gold') / 1000));
            set_module_objpref('clans', $clanid, 'treepoints', $points);
            $besttree = get_module_setting('besttree');

            if ($points > get_module_objpref('clans', $besttree, 'treepoints'))
            {
                set_module_setting('besttree', $clanid);

                if ($clanid != $besttree)
                {
                    $params['contribution'] = true;
                }
            }
        }
    }

    LotgdResponse::pageAddContent(LotgdTheme::render('@module/clantrees/run.twig', $params));

    LotgdResponse::pageEnd();
}
