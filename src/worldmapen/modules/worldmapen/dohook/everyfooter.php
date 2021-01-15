<?php

if ('worldmapen' == $args['script'])
{
    $helper = \LotgdKernel::get(Laminas\View\Helper\HeadLink::class);
    $helper->appendStylesheet('css/module/worldmapen.css');
}

if ('gypsy' == $args['script']&& 1 == get_module_setting('worldmapAcquire') && ( ! isset($op) || '' == $op))
{
    \LotgdNavigation::addHeader('navigation.category.map');
    \LotgdNavigation::addNav('navigation.nav.gypsy.ask', 'runmodule.php?module=worldmapen&op=gypsy');
}
