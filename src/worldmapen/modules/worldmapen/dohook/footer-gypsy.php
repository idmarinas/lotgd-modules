<?php

if (1 == get_module_setting('worldmapAcquire') && (! isset($op) || '' == $op))
{
    \LotgdNavigation::addHeader('navigation.category.map');
    \LotgdNavigation::addNav('navigation.nav.gypsy.ask', 'runmodule.php?module=worldmapen&op=gypsy');
}
