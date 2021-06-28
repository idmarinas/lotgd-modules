<?php

require_once 'lib/partner.php';

$textDomain = modulehook('drinks-text', ['textDomain' => 'drinks-module']);
$textDomain = $textDomain['textDomain'];

$repository = \Doctrine::getRepository('LotgdLocal:ModuleDrinks');
$drunk      = get_module_pref('drunkeness');
$maxDrunk   = get_module_setting('maxdrunk');

//-- Change text domain for navigation
\LotgdNavigation::setTextDomain($textDomain);

$params = [
    'textDomain' => $textDomain,
    'innName'    => getsetting('innname', LOCATION_INN),
    'drunk'      => $drunk,
    'maxDrunk'   => $maxDrunk,
    'drunkeness' => (int) ($drunk > $maxDrunk),
    'partner'    => get_partner(),
    'barkeep'    => getsetting('barkeep', '`tCedrik`0'),
];

\LotgdResponse::pageStart('section.ale.drink.title', ['innName' => \LotgdSanitize::fullSanitize($params['innName'])], $textDomain);

if ( ! $params['drunkeness'])
{
    $drinkId   = (int) \LotgdRequest::getQuery('id');
    $entity    = $repository->find($drinkId);
    $row       = $repository->extractEntity($entity);
    $drinkCost = $session['user']['level'] * $row['costperlevel'];

    $params['drinkName'] = $row['name'];
    $params['cost']      = $drinkCost;

    $params['buyed'] = false;

    if ($session['user']['gold'] >= $drinkCost)
    {
        $params['buyed'] = true;

        $drunk += $row['drunkeness'];
        set_module_pref('drunkeness', $drunk);

        $session['user']['gold'] -= $drinkcost;

        \LotgdLog::debug("spent {$drinkcost} on {$row['name']}");

        if ($row['harddrink'])
        {
            $drinks = get_module_pref('harddrinks');
            set_module_pref('harddrinks', $drinks + 1);
        }

        $givehp   = 0;
        $giveturn = 0;

        if ($row['hpchance'] > 0 || $row['turnchance'] > 0)
        {
            $tot = $row['hpchance'] + $row['turnchance'];
            $c   = \mt_rand(1, \max(1, $tot));

            $giveturn = 1;

            if ($c <= $row['hpchance'] && $row['hpchance'] > 0)
            {
                $givehp   = 1;
                $giveturn = 0;
            }
        }

        $givehp   = $row['alwayshp'] ? 1 : $givehp;
        $giveturn = $row['alwaysturn'] ? 1 : $giveturn;

        if ($giveturn)
        {
            $turns               = e_rand($row['turnmin'], $row['turnmax']);
            $params['vigorous']  = (0 < $turns);
            $params['lethargic'] = (0 > $turns);

            $params['staminaSystem'] = is_module_active('staminasystem');

            if ($params['staminaSystem'])
            {
                require_once 'modules/staminasystem/lib/lib.php';

                if (0 < $turns)
                {
                    $stamina = $turns * 25000;
                    addstamina($stamina);
                }
                elseif (0 > $turns)
                {
                    $stamina = $turns * 25000;
                    removestamina($stamina);
                }
            }
            else
            {
                $oldturns = $session['user']['turns'];

                $session['user']['turns'] += $turns;
                $session['user']['turns'] = \max($session['user']['turns'], 0);

                $params['vigorous']  = ($oldturns < $session['user']['turns']);
                $params['lethargic'] = ($oldturns > $session['user']['turns']);
            }
        }

        if ($givehp)
        {
            $oldhp = $session['user']['hitpoints'];

            $hp = \mt_rand($row['hpmin'], $row['hpmax']);
            // Check for percent increase first
            if (0.0 != $row['hppercent'])
            {
                $hp = \round($session['user']['maxhitpoints'] * ($row['hppercent'] / 100), 0);
            }

            $session['user']['hitpoints'] += $hp;
            $session['user']['hitpoints'] = \max($session['user']['hitpoints'], 1);

            $params['healthy'] = ($oldhp < $session['user']['hitpoints']);
            $params['sick']    = ($oldhp > $session['user']['hitpoints']);
        }

        $buff           = [];
        $buff['name']   = $row['buffname'];
        $buff['rounds'] = $row['buffrounds'];

        $buff['wearoff']        = $row['buffwearoff'] ?: null;
        $buff['atkmod']         = $row['buffatkmod'] ?: null;
        $buff['defmod']         = $row['buffdefmod'] ?: null;
        $buff['dmgmod']         = $row['buffdmgmod'] ?: null;
        $buff['damageshield']   = $row['buffdmgshield'] ?: null;
        $buff['roundmsg']       = $row['buffroundmsg'] ?: null;
        $buff['effectmsg']      = $row['buffeffectmsg'] ?: null;
        $buff['effectnodmgmsg'] = $row['buffeffectnodmgmsg'] ?: null;
        $buff['effectfailmsg']  = $row['buffeffectfailmsg'] ?: null;
        $buff['schema']         = 'drinks-module';

        apply_buff('buzz', $buff);
    }

    $params['drink'] = $row;
}

\LotgdNavigation::addNav('navigation.nav.return.inn', 'inn.php', ['textDomain' => $textDomain]);
\LotgdNavigation::addNav('navigation.nav.return.talk', 'inn.php?op=bartender', [
    'textDomain' => $textDomain,
    'params'     => ['barkeep' => $params['barkeep']],
]);

\LotgdNavigation::villageNav();

\LotgdResponse::pageAddContent(\LotgdTheme::render('@module/drinks/run/buy.twig', $params));
