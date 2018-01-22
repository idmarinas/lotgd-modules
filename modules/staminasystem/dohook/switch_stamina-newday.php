<?php

$spirits = isset($args['spirits']) ? $args['spirits'] : 0;

if($spirits > 0)
{
	$stamina = $spirits*25000;
	addstamina($stamina);
	debug("Turns Added");
}
else if (0 != $spirits)
{
    $stamina = (abs($spirits)*25000)*2;
	removestamina($stamina);
	debug("Turns Removed");
}
