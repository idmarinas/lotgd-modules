<?php

if ('World' == $session['user']['location'])
{
    \LotgdNavigation::addNav('navigation.nav.mundane', 'runmodule.php?module=worldmapen&op=continue', [ 'textDomain' => 'module-worldmapen' ]);
    $args['handled'] = 1;
}
