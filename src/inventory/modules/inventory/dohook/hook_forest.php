<?php

//-- Change text domain
\LotgdNavigation::setTextDomain($textDomain);
\LotgdNavigation::addHeader('navigation.category.inventory');

display_item_nav('forest', 'forest.php');

//-- Restore text domain
\LotgdNavigation::setTextDomain();
