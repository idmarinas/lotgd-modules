<?php 

$spirits = $args['spirits'];

if($spirits>0 && 0 != $spirits)
{
	$stamina = $spirits*25000;
	addstamina($stamina);
	debug("Turns Added");
}
else
{
	$stamina = abs($spirits)*25000;
	removestamina($stamina);
	debug("Turns Removed");
}