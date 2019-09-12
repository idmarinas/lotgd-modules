<?php

if ('World' == $session['user']['location'])
{
    addnav('M?Return to the Mundane', 'runmodule.php?module=worldmapen&op=continue');
    $args['handled'] = 1;
}
