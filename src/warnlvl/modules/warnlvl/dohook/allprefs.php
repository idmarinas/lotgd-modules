<?php

if ($session['user']['superuser'] & SU_EDIT_USERS)
{
    $id = \LotgdHttp::getQuery('userid');

    \LotgdNavigation::addHeader('navigation.nav.editors', [ 'textDomain' => 'module-allprefseditor']);
    \LotgdNavigation::addNav('navigation.nav.level', "runmodule.php?module=warnlvl&op=superuser&subop=edit&userid={$id}",[ 'textDomain' => 'module-warnlvl']);
}
