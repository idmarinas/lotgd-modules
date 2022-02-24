<?php

$flirt = (int) LotgdRequest::getQuery('flirt');

$params['flirt']         = $flirt;
$params['seenLover']     = get_module_pref('seenlover');
$params['staminaSystem'] = is_module_active('staminasystem');
$params['married']       = (INT_MAX == $session['user']['marriedto']);

if ($params['staminaSystem'])
{
    require_once 'modules/staminasystem/lib/lib.php';
}

if ( ! $params['seenLover'])
{
    if ($params['married'])
    {
        if (1 == e_rand(1, 4))
        {
            --$session['user']['charm'];
        }
        else
        {
            LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('lover', lovers_getbuff());
            ++$session['user']['charm'];
        }

        $params['seenLover'] = 1;
    }
    elseif ( $flirt === 0)
    {
        LotgdNavigation::addHeader('navigation.nav.flirt');
        LotgdNavigation::addNav('navigation.nav.flirt.barmaid.wink', 'runmodule.php?module=lovers&op=flirt&flirt=1');
        LotgdNavigation::addNav('navigation.nav.flirt.barmaid.kiss', 'runmodule.php?module=lovers&op=flirt&flirt=2');
        LotgdNavigation::addNav('navigation.nav.flirt.barmaid.pack', 'runmodule.php?module=lovers&op=flirt&flirt=3');
        LotgdNavigation::addNav('navigation.nav.flirt.barmaid.sit', 'runmodule.php?module=lovers&op=flirt&flirt=4');
        LotgdNavigation::addNav('navigation.nav.flirt.barmaid.grab', 'runmodule.php?module=lovers&op=flirt&flirt=5');
        LotgdNavigation::addNav('navigation.nav.flirt.barmaid.carry', 'runmodule.php?module=lovers&op=flirt&flirt=6');
        LotgdNavigation::addNav('navigation.nav.flirt.barmaid.marry', 'runmodule.php?module=lovers&op=flirt&flirt=7');
    }
    else
    {
        $c                   = $session['user']['charm'];
        $params['seenLover'] = 1;

        switch ($flirt)
        {
            case 1:
                    $params['flirtCase'] = 2;

                    if (e_rand($c, 2) >= 2)
                    {
                        $params['flirtCase'] = 1;

                        if ($c < 4)
                        {
                            ++$c;
                        }
                    }
            break;
            case 2:
                    $params['flirtCase'] = 2;

                    if (e_rand($c, 4) >= 4)
                    {
                        $params['flirtCase'] = 1;

                        if ($c < 7)
                        {
                            ++$c;
                        }
                    }
            break;
            case 3:
                    $params['flirtCase'] = 2;

                    if (e_rand($c, 7) >= 7)
                    {
                        $params['flirtCase'] = 1;

                        if ($c < 11)
                        {
                            ++$c;
                        }
                    }
            break;
            case 4:
                    $params['flirtCase'] = 2;

                    if (e_rand($c, 11) >= 11)
                    {
                        $params['flirtCase'] = 1;

                        if ($c < 14)
                        {
                            ++$c;
                        }
                    }
                    else
                    {
                        if ($c > 0 && $c < 10)
                        {
                            --$c;
                        }
                    }
            break;
            case 5:
                    $params['flirtCase'] = 2;

                    if (e_rand($c, 14) >= 14)
                    {
                        $params['flirtCase'] = 1;

                        if ($c < 18)
                        {
                            ++$c;
                        }
                    }
                    else
                    {
                        if ($c > 0 && $c < 13)
                        {
                            --$c;
                        }
                    }
            break;
            case 6:
                    $params['flirtCase'] = 2;

                    if (e_rand($c, 18) >= 18)
                    {
                        $params['flirtCase'] = 1;

                        if ($params['staminaSystem'])
                        {
                            removestamina(50000);
                        }
                        else
                        {
                            $session['user']['turns'] -= 2;

                            $session['user']['turns'] = \max(0, $session['user']['turns']);
                        }

                        LotgdLog::addNews('news.flirt.barmaid.inn', [
                            'playerName' => $session['user']['name'],
                            'partner'    => $partner,
                        ], $textDomain);

                        if ($c < 25)
                        {
                            ++$c;
                        }
                    }
                    else
                    {
                        if ($c > 0)
                        {
                            --$c;
                        }
                    }
            break;
            case 7:
                $params['flirtCase'] = 2;

                if ($c >= 22)
                {
                    $params['flirtCase'] = 1;

                    LotgdLog::addNews('news.flirt.barmaid.matrimony', [
                        'playerName' => $session['user']['name'],
                        'partner'    => $partner,
                    ], $textDomain);

                    $session['user']['marriedto'] = INT_MAX;
                    LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('lover', lovers_getbuff());
                }
                else
                {
                    if ($params['staminaSystem'])
                    {
                        removestamina(500000);
                    }
                    else
                    {
                        $session['user']['turns'] = 0;
                    }

                    LotgdLog::debug('lost all turns after being rejected for marriage.');
                }
            break;
        }

        if ($c > $session['user']['charm'])
        {
            $params['charmGain'] = true;
        }
        elseif ($c < $session['user']['charm'])
        {
            $params['charmLost'] = true;
        }

        $session['user']['charm'] = $c;
    }
}

set_module_pref('seenlover', $params['seenLover']);
