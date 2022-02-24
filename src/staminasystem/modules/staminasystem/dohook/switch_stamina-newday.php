<?php

$spirits = $args['spirits'] ?? 0;

if ($spirits > 0)
{
    $stamina = $spirits * 25000;
    addstamina($stamina);
    LotgdResponse::pageDebug('Turns Added');
}
elseif ($spirits < 0)
{
    $stamina = (\abs($spirits) * 25000) * 2;
    removestamina($stamina);
    LotgdResponse::pageDebug('Turns Removed');
}
