<?php
	tlschema($args['schemas']['gatenav']);
	addnav($args['gatenav']);
	tlschema();
	addnav(get_module_setting("villagenav"),"runmodule.php?module=dwellings");
	set_module_pref("dwelling_saver",0);
?>