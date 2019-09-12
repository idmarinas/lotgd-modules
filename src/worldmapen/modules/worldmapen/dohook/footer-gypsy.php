<?php

if (1 == get_module_setting('worldmapAcquire') && (! isset($op) || '' == $op))
{
    addnav('Map');
    addnav('Ask about World Map', 'runmodule.php?module=worldmapen&op=gypsy');
}
