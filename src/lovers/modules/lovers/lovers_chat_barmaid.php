<?php

$act = (string) \LotgdHttp::getQuery('act');
$params['act'] = $act;

if (! $act)
{
    \LotgdNavigation::addNav('navigation.nav.chat.barmaid.gossip', 'runmodule.php?module=lovers&op=chat&act=gossip');
    \LotgdNavigation::addNav('navigation.nav.chat.barmaid.fat', 'runmodule.php?module=lovers&op=chat&act=fat', [
        'params' => ['armor' => $session['user']['armor']]
    ]);
}
elseif ('fat' == $act)
{
    $charm = $session['user']['charm'] + mt_rand(-1, 1);

    switch ($charm)
    {
        case -3: case -2: case -1: case 0:
            $params['charmMsg'] = 1;
        break;
        case 1: case 2: case 3:
            $params['charmMsg'] = 2;
        break;
        case 4: case 5: case 6:
            $params['charmMsg'] = 3;
        break;
        case 7: case 8: case 9:
            $params['charmMsg'] = 4;
        break;
        case 10: case 11: case 12:
            $params['charmMsg'] = 5;
        break;
        case 13: case 14: case 15:
            $params['charmMsg'] = 6;
        break;
        case 16: case 17: case 18:
            $params['charmMsg'] = 7;
        break;
        default:
            $params['charmMsg'] = 0;
        break;
    }
}
