<?php

if (isset($session['user']['location']) && 'World' == $session['user']['location'])
{
    LotgdNavigation::addNav('navigation.nav.world', 'runmodule.php?module=worldmapen&op=continue');
    $args['handled'] = 1;
}
