<?php

if ($session['user']['superuser'] & SU_EDIT_USERS)
{
    //-- Change text domain
    \LotgdNavigation::setTextDomain($textDomain);
    \LotgdNavigation::addHeader('navigation.category.inventory');

    $return = \urlencode(\LotgdSanitize::cmdSanitize(\LotgdRequest::getServer('REQUEST_URI')));
    \LotgdNavigation::addNav('navigation.nav.inventory', "runmodule.php?module=inventory&op=superuser&acctid={$args['acctid']}&return={$return}");

    //-- Restore text domain
    \LotgdNavigation::setTextDomain();
}
