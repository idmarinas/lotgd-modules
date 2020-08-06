<?php

//-- Change text domain
\LotgdNavigation::setTextDomain($textDomain);
\LotgdNavigation::addHeader('navigation.category.inventory');

if (get_module_setting('inventorylink'))
{
    \LotgdNavigation::addNav('navigation.nav.inventory', 'runmodule.php?module=inventory');
}

display_item_nav('village', 'village.php');

//-- Restore text domain
\LotgdNavigation::setTextDomain();
