<?php

if(!(is_array(unserialize(get_module_setting("actionsarray"))))){
	set_module_setting("actionsarray",serialize(array()),"staminasystem");
}

module_addhook_priority("everyfooter",0);
module_addhook_priority("charstats",99);
module_addhook("superuser");
module_addhook_priority("newday",99);
module_addhook("dragonkill");
module_addhook("stamina-newday");
module_addhook("process-create");


// UPDATE `lotgd_alpha`.`module_userprefs` SET `value`='a:0:{}' WHERE  `modulename`='staminasystem' AND `setting`='buffs' AND `value`= 'array()';
?>