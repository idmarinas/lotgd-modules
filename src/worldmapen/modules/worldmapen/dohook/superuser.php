<?php

if (($session['user']['superuser'] & SU_EDIT_USERS) || get_module_pref('canedit'))
{
    \LotgdNavigation::addHeader('superuser.category.module', ['textDomain' => 'navigation-app']);
    \LotgdNavigation::addNav('navigation.nav.editor.superuser', 'runmodule.php?module=worldmapen&op=edit&admin=true', ['textDomain' => 'module-worldmapen']);
}
