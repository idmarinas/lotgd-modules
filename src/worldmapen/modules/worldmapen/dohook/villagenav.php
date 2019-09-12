<?php

if (isset($session['user']['location']) && 'World' == $session['user']['location'])
{
    addnav('V?Return to the World', 'runmodule.php?module=worldmapen&op=continue');
    $args['handled'] = 1;
}
