<?php
//-- Change text domain
\LotgdNavigation::setTextDomain($textDomain);
\LotgdNavigation::addHeader('navigation.category.inventory');

display_item_nav('shades', 'shades.php');

//-- Restore text domain
\LotgdNavigation::setTextDomain();
