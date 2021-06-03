<?php

/* ver 1.04 by Matt Mullen matt@mattmullen.net */
/* Thanks to Shannon Brown's code to guide in LoGD API */
/* 1 October 2005 */

function ramiusaltar_getmoduleinfo()
{
    return [
        'name'     => 'Alter to Ramius',
        'version'  => '2.1.1',
        'author'   => '`7ma`0`&tt`0`3@`0`7matt`0`&mullen`0`3.`0`7net`0, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Village',
        'download' => 'http://www.mattmullen.net',
        'settings' => [
            'reward = minimumguaranteedreward + random(0;maximumrandombonus),note',
            'ramiusaltarloc'   => 'Where does the altar to Ramius appear,location|'.getsetting('villagename', LOCATION_FIELDS),
            'sacrificesperday' => 'How many times can the user sacrifice each day,int|1',
            'reward1'          => 'What is the minimum guaranteed favor for blood sacrafice,int|10',
            'reward2'          => 'What is the minimum guaranteed favor for flesh sacrafice,int|35',
            'reward3'          => 'What is the minimum guaranteed favor for spirit sacrafice,int|65',
            'rewardbonus'      => 'What is the maximum random bonus for any sacrafice,int|35',
        ],
        'prefs' => [
            'sacrificedtoday' => 'How many times has the user sacrificed today,int|0',
            'totalgained'     => 'How much total favor has the user gained,int|0',
            'totalsacrifices' => 'How many times has the user ever sacrificed,int|0',
            'totalhploss'     => 'How many total hitpoints has the user lost,int|0',
            'totalturnloss'   => 'How many total turns has the user lost,int|0',
            'totalmaxhploss'  => 'How many total max hitpoints has the user lost,int|0',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function ramiusaltar_install()
{
    module_addhook('changesetting');
    module_addhook('village');
    module_addhook('newday');
    module_addhook('footer-hof');

    return true;
}

function ramiusaltar_uninstall()
{
    return true;
}

function ramiusaltar_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'changesetting':
            if ('villagename' == $args['setting'] && $args['old'] == get_module_setting('ramiusaltarloc'))
            {
                set_module_setting('ramiusaltarloc', $args['new']);
            }
        break;

        case 'village':
            if ($session['user']['location'] == get_module_setting('ramiusaltarloc'))
            {
                \LotgdNavigation::addHeader('headers.fight');
                \LotgdNavigation::addNav('navigation.nav.altar', 'runmodule.php?module=ramiusaltar', ['textDomain' => 'module_ramiusaltar']);
            }
        break;

        case 'newday':
            set_module_pref('sacrificedtoday', 0);
        break;

        case 'footer-hof':
            \LotgdNavigation::addHeader('category.ranking', ['textDomain' => 'navigation_hof']);
            \LotgdNavigation::addNav('navigation.nav.rank', 'runmodule.php?module=ramiusaltar&op=HOF', ['textDomain' => 'module_ramiusaltar']);
        break;
        default: break;
    }

    return $args;
}

function ramiusaltar_run()
{
    global $session;

    $op   = \LotgdRequest::getQuery('op');
    $type = \LotgdRequest::getQuery('type');

    $textDomain = 'module_ramiusaltar';
    $useStamina = is_module_active('staminasystem');

    \LotgdResponse::pageStart('title', [], $textDomain);

    $params = [
        'textDomain' => $textDomain,
        'useStamina' => $useStamina,
    ];

    \LotgdNavigation::setTextDomain($textDomain);

    \LotgdNavigation::addHeader('common.category.navigation', ['textDomain' => 'navigation_app']);

    if ('' == $op)
    {
        $params['tpl']        = 'default';
        $params['sacrificed'] = false;

        if (get_module_pref('sacrificedtoday') >= get_module_setting('sacrificesperday'))
        {
            $params['sacrificed'] = true;
        }
        else
        {
            \LotgdNavigation::addNav('navigation.nav.default.blood', 'runmodule.php?module=ramiusaltar&op=give&type=blood');
            \LotgdNavigation::addNav('navigation.nav.default.flesh', 'runmodule.php?module=ramiusaltar&op=give&type=flesh');
            \LotgdNavigation::addNav('navigation.nav.default.spirit', 'runmodule.php?module=ramiusaltar&op=give&type=spirit');
            \LotgdNavigation::addNav('navigation.nav.default.defile', 'runmodule.php?module=ramiusaltar&op=defile');
        }
    }
    elseif ('give' == $op)
    {
        $params['tpl']     = 'give';
        $gain_favor        = \mt_rand(0, get_module_setting('rewardbonus'));
        $ramius_is_pleased = 1;

        switch ($type)
        {
            case 'blood':
                $params['giveType'] = 'blood';
                $params['weak']     = false;

                if ($session['user']['hitpoints'] <= $session['user']['maxhitpoints'] * 0.75)
                {
                    $params['weak'] = true;
                    debuglog('lost 5 favor trying to give blood with '.$session['user']['hitpoints'].' of '.$session['user']['maxhitpoints'].' maxhp left');
                    $ramius_is_pleased = 0;

                    break;
                }

                $gain_favor += get_module_setting('reward1');

                $params['favor'] = $gain_favor;

                set_module_pref('totalhploss', get_module_pref('totalhploss') + $session['user']['hitpoints'] * 0.9);
                $session['user']['hitpoints'] *= 0.1;

                debuglog('gained `4'.$gain_favor.' favor`7 giving blood at Altar of Ramius');
            break;
            case 'flesh':
                $params['giveType'] = 'flesh';
                $params['weak']     = false;

                if ($useStamina)
                {
                    require_once 'modules/staminasystem/lib/lib.php';
                    $amber = get_stamina();
                }

                if ($session['user']['turns'] <= 4 || ($useStamina && $amber < 100))
                {
                    $params['weak'] = true;
                    debuglog('lost 5 favor trying to give flesh with '.($useStamina ? get_stamina(3).'stamina' : $session['user']['turns'].'turns').' left');
                    $ramius_is_pleased = 0;

                    break;
                }

                $gain_favor += get_module_setting('reward2');

                $turn_loss = \mt_rand(2, 4);

                if ($useStamina)
                {
                    $stamina = $turn_loss * 25000;
                    set_module_pref('totalturnloss', get_module_pref('totalturnloss') + $stamina);
                    removestamina($stamina);
                    debuglog('lost `@'.$stamina.' stamina `7 giving spirit at Altar of Ramius');
                }
                else
                {
                    $session['user']['turns'] -= $turn_loss;
                    set_module_pref('totalturnloss', get_module_pref('totalturnloss') + $turn_loss);
                    debuglog('lost `@'.$turn_loss.' turns `7 giving spirit at Altar of Ramius');
                }

                $param['turnsLost'] = $turn_loss;

                debuglog('gained `4'.$gain_favor.' favor`7 giving spirit at Altar of Ramius');
            break;
            case 'spirit':
                $params['giveType'] = 'spirit';
                $params['weak']     = false;

                if ($session['user']['permahitpoints'] < 0)
                {
                    $params['weak'] = true;
                    debuglog('lost 5 favor trying to give spirit with '.$session['user']['maxhitpoints'].' hp at lvl '.$session['user']['level'].'.');
                    $ramius_is_pleased = 0;

                    break;
                }

                $gain_favor += get_module_setting('reward3');

                $hp_loss = \mt_rand(1, 3);
                $session['user']['maxhitpoints'] -= $hp_loss;
                $session['user']['hitpoints']    -= $hp_loss;
                set_module_pref('totalmaxhploss', get_module_pref('totalmaxhploss') + $hp_loss);

                $session['user']['permahitpoints'] -= $hp_loss;

                $params['hpLost'] = $hp_loss;

                debuglog('gained `4'.$gain_favor.' favor`7 giving spirit at Altar of Ramius');
                debuglog('lost `&'.$hp_loss.' max hp `7 giving spirit at Altar of Ramius');
            break;
            default:
                $params['giveType'] = '';

                \LotgdNavigation::villageNav();
            break;
        } // end switch ($type)

        $params['ramiusIsPleased'] = $ramius_is_pleased;

        if ($ramius_is_pleased && $params['giveType'])
        {
            $params['favorGain'] = $gain_favor;

            if (is_module_active('alignment'))
            {
                require_once 'modules/alignment/func.php';
                align(-1);
            }

            $session['user']['deathpower'] += $gain_favor;
            set_module_pref('totalgained', get_module_pref('totalgained') + $gain_favor);	//200
            set_module_pref('totalsacrifices', get_module_pref('totalsacrifices') + 1);
            set_module_pref('sacrificedtoday', get_module_pref('sacrificedtoday') + 1);
        }
        elseif ($params['giveType'])
        {
            \LotgdNavigation::addNav('navigation.nav.give.return', 'runmodule.php?module=ramiusaltar');

            $session['user']['deathpower'] -= 5;

            $session['user']['deathpower'] = \max(0, $session['user']['deathpower']);
        }
    }
    elseif ('defile' == $op)
    {
        $params['tpl']     = 'defile';
        $params['defiled'] = false;

        if ($session['user']['deathpower'] > 0)
        {
            $favor_loss          = \min(50, $session['user']['deathpower']);
            $params['defiled']   = true;
            $params['favorLost'] = $favor_loss;

            $session['user']['deathpower'] -= $favor_loss;

            if (is_module_active('alignment'))
            {
                require_once 'modules/alignment/func.php';
                align(2);  // +1 aligment
            }

            if ($useStamina)
            {
                require_once 'modules/staminasystem/lib/lib.php';
                addstamina(25000);
            }
            else
            {
                ++$session['user']['turns'];
            }
        }
    }
    elseif ('hof' == \strtolower($op))
    {
        $params['tpl'] = 'hof';

        \LotgdNavigation::addNav('navigation.nav.hof.back', 'hof.php');

        $page = (int) \LotgdRequest::getQuery('page');

        $repository = \Doctrine::getRepository('LotgdCore:Accounts');
        $query      = $repository->createQueryBuilder('u');
        $expr       = $query->expr();

        $query->select('c.name', 'g.value AS gained', 's.value AS sacrifices', 'h.value AS hpLost', 't.value AS turns', 'm.value AS maxHpLost')
            ->innerJoin('LotgdCore:Characters', 'c', 'with', $expr->eq('c.acct', 'u.acctid'))

            ->innerJoin('LotgdCore:ModuleUserprefs', 's', 'with', $expr->andX(
                $expr->eq('s.userid', 'u.acctid'),
                $expr->eq('s.modulename', $expr->literal('ramiusaltar')),
                $expr->eq('s.setting', $expr->literal('totalsacrifices'))
            ))

            ->leftJoin('LotgdCore:ModuleUserprefs', 'g', 'with', $expr->andX(
                $expr->eq('g.userid', 'u.acctid'),
                $expr->eq('g.modulename', $expr->literal('ramiusaltar')),
                $expr->eq('g.setting', $expr->literal('totalgained'))
            ))

            ->leftJoin('LotgdCore:ModuleUserprefs', 'h', 'with', $expr->andX(
                $expr->eq('h.userid', 'u.acctid'),
                $expr->eq('h.modulename', $expr->literal('ramiusaltar')),
                $expr->eq('h.setting', $expr->literal('totalhploss'))
            ))

            ->leftJoin('LotgdCore:ModuleUserprefs', 't', 'with', $expr->andX(
                $expr->eq('t.userid', 'u.acctid'),
                $expr->eq('t.modulename', $expr->literal('ramiusaltar')),
                $expr->eq('t.setting', $expr->literal('totalturnloss'))
            ))

            ->leftJoin('LotgdCore:ModuleUserprefs', 'm', 'with', $expr->andX(
                $expr->eq('m.userid', 'u.acctid'),
                $expr->eq('m.modulename', $expr->literal('ramiusaltar')),
                $expr->eq('m.setting', $expr->literal('totalmaxhploss'))
            ))

            ->where('u.locked = 0 AND BIT_AND(u.superuser, :permit) = 0 AND s.value > 0')

            ->orderBy('s.value', 'DESC')
            ->addOrderBy('c.name', 'DESC')

            ->setParameter('permit', SU_HIDE_FROM_LEADERBOARD)
        ;

        $params['paginator'] = $repository->getPaginator($query, $page);
    }

    $params['weapon'] = $session['user']['weapon'];
    $params['name']   = $session['user']['name'];

    \LotgdNavigation::addHeader('common.category.return', ['textDomain' => 'navigation_app']);
    \LotgdNavigation::villageNav();

    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/ramiusaltar/run.twig', $params));

    \LotgdResponse::pageEnd();
}
