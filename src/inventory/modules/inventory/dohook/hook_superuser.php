<?php

global $session;

if (($session['user']['superuser'] & SU_EDIT_CONFIG) !== 0)
{
    LotgdNavigation::addHeader('superuser.category.editors', ['textDomain' => 'navigation_app']);
    LotgdNavigation::addNav('navigation.nav.editor', 'runmodule.php?module=inventory&op=editor', ['textDomain' => $textDomain]);
}
