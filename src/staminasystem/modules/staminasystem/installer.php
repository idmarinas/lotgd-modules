<?php

if ( ! (\is_array(\unserialize(get_module_setting('actionsarray')))))
{
    set_module_setting('actionsarray', \serialize([]), 'staminasystem');
}

module_addhook_priority('everyfooter', 0);
module_addhook_priority('charstats', 99);
module_addhook('superuser');
module_addhook_priority('newday', 99);
module_addhook('stamina-newday');
module_addhook('process-create');
