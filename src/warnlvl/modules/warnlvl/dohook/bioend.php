<?php

$id = $args['acctid'];

$yomWarning = ($session['user']['superuser'] & SU_GIVES_YOM_WARNING);

if ($yomWarning)
{
    \LotgdNavigation::addHeader('navigation.category.warning');
    \LotgdNavigation::addNav('navigation.nav.warn', 'runmodule.php?module=warnlvl&op=warnplayer&id='.$id.'&ret='.urlencode($_SERVER['REQUEST_URI']));
}


