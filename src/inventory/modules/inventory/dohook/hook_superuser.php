<?php

global $session;

if ($session['user']['superuser'] & SU_EDIT_CONFIG)
{
    \LotgdNavigation::addHeader('superuser.category.editors', [ 'textDomain' => 'navigation-app' ]);
    \LotgdNavigation::addNav('navigation.nav.editor', 'runmodule.php?module=inventory&op=editor', [ 'textDomain' => $textDomain ]);
}
