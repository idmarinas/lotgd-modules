<?php

//addnews ready
// mail ready
// translator ready

function lottery_getmoduleinfo()
{
    return [
        'name'     => "Cedrik's Lottery",
        'version'  => '3.0.0',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Inn',
        'download' => 'core_module',
        'settings' => [
            'Village Lottery Settings,title',
            'basepot'        => 'How much gold is the base pot?,int|1000',
            'ticketcost'     => 'How much gold does a ticket cost?,int|100',
            'percentbleed'   => 'Percentage to keep for injured forest creatures,range,1,100,1|50',
            'currentjackpot' => 'Current Jackpot,int|0',
            'roundnum'       => 'Lottery Round Number,viewonly|0',
            'Past Round Data,title',
            'todaysnumbers' => 'Last numbers,viewonly|2689',
            'prize'         => 'Last Jackpot,int|0',
            'howmany'       => 'How many people was this prize split among?,int|0',
        ],
        'prefs' => [
            'Village Lottery User Preferences,title',
            'pick'     => 'Numbers chosen,|',
            'roundnum' => 'Round the numbers were chosen in,int|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function lottery_install()
{
    module_addhook('newday');
    module_addhook('newday-runonce');
    module_addhook('inn');

    return true;
}

function lottery_uninstall()
{
    return true;
}

function lottery_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'newday':
            $numbers  = get_module_setting('todaysnumbers');
            $roundnum = get_module_setting('roundnum');
            $pround   = get_module_pref('roundnum');

            $params = [
                'textDomain' => 'module_lottery',
                'numbers'    => $numbers,
                'n0'         => $numbers[0],
                'n1'         => $numbers[1],
                'n2'         => $numbers[2],
                'n3'         => $numbers[3],
            ];

            if ($roundnum > $pround)
            {
                if (get_module_pref('pick') == $numbers)
                {
                    $prize = get_module_setting('prize');

                    if ($prize > '' && $pround < $roundnum)
                    {
                        $params['winner'] = true;
                        $params['prize']  = $prize;

                        $session['user']['goldinbank'] += $prize;
                        LotgdLog::debug("won {$prize} gold on lottery");

                        LotgdLog::addNews('news.winner',
                            [
                                'playerName' => $session['user']['name'],
                                'prize'      => $prize,
                            ],
                            'module_lottery'
                        );
                    }
                }

                set_module_pref('pick', '');
            }

            $args['includeTemplatesPost']['@module/lottery/dohook/newday.twig'] = $params;
        break;
        case 'newday-runonce':
            $numbers[0] = \mt_rand(0, 9);
            $numbers[1] = \mt_rand(0, 9);
            $numbers[2] = \mt_rand(0, 9);
            $numbers[3] = \mt_rand(0, 9);

            \sort($numbers);

            $numbers = \implode('', $numbers);

            set_module_setting('todaysnumbers', $numbers);

            $repository = Doctrine::getRepository('LotgdCore:ModuleUserprefs');
            $winners    = $repository->count(['modulename' => 'lottery', 'setting' => 'pick', 'value' => $numbers]);

            if ($winners)
            {
                //split the jackpot among winners.
                $prize = \max(1, \floor(get_module_setting('currentjackpot') / $winners));
                set_module_setting('prize', $prize);
                set_module_setting('currentjackpot', get_module_setting('basepot'));
                set_module_setting('howmany', $winners);
            }
            else
            {
                //the jackpot rolls over.
                set_module_setting('prize', 0);
                set_module_setting('howmany', 0);
            }

            set_module_setting('roundnum', get_module_setting('roundnum') + 1);
        break;
        case 'inn':
            LotgdNavigation::addHeader('category.do');
            LotgdNavigation::addNav('navigation.nav.lottery', 'runmodule.php?module=lottery&op=store', [
                'textDomain' => 'module_lottery',
                'params'     => ['barman' => LotgdSetting::getSetting('barkeep', '`tCedrik`0')],
            ]);
        break;
        default: break;
    }

    return $args;
}

function lottery_run()
{
    global $session;

    $op       = (string) LotgdRequest::getQuery('op');
    $cost     = get_module_setting('ticketcost');
    $numbers  = get_module_setting('todaysnumbers');
    $prize    = get_module_setting('prize');
    $winners  = get_module_setting('howmany');
    $jackpot  = (int) get_module_setting('currentjackpot');
    $bleed    = (int) get_module_setting('percentbleed');
    $roundnum = (int) get_module_setting('roundnum');

    $textDomain = 'module_lottery';

    if ('buy' == $op)
    {
        $op = 'store';
        LotgdRequest::setQuery('op', $op);

        $message = 'flash.message.error.gold';

        if ($session['user']['gold'] >= $cost)
        {
            $lotto = LotgdRequest::getPost('lotto');

            $message = 'flash.message.error.lotto';

            if ($lotto)
            {
                $message = null;
                \sort($lotto);
                set_module_pref('pick', \implode('', $lotto));
                set_module_pref('roundnum', $roundnum);
                $session['user']['gold'] -= $cost;
                LotgdLog::debug("spent {$cost} on a lottery ticket");
                $jackpot += \round($cost * (100 - $bleed) / 100, 0);
                set_module_setting('currentjackpot', $jackpot);
            }
        }

        if ($message)
        {
            LotgdFlashMessages::addErrorMessage(LotgdTranslator::t($message, [], $textDomain));
        }
    }

    $params = [
        'textDomain' => $textDomain,
        'barman'     => LotgdSetting::getSetting('barkeep', '`tCedrik`0'),
        'n0'         => $numbers[0],
        'n1'         => $numbers[1],
        'n2'         => $numbers[2],
        'n3'         => $numbers[3],
        'prize'      => $prize,
        'cost'       => $cost,
    ];

    LotgdResponse::pageStart('title', ['barman' => $params['barman']], $textDomain);

    $params['jackpot'] = $jackpot;
    $params['winners'] = $winners;
    $params['pick']    = get_module_pref('pick');
    $params['pn0']     = $params['pick'][0];
    $params['pn1']     = $params['pick'][1];
    $params['pn2']     = $params['pick'][2];
    $params['pn3']     = $params['pick'][3];

    LotgdNavigation::addHeader('navigation.category.return', ['textDomain' => $textDomain]);
    LotgdNavigation::addNav('navigation.nav.inn', 'inn.php', ['textDomain' => $textDomain]);

    LotgdNavigation::villageNav();

    LotgdResponse::pageAddContent(LotgdTheme::render('@module/lottery/run.twig', $params));

    LotgdResponse::pageEnd();
}
