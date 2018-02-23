<?php
/**************
Name: Equipment Buffs, for the Equipment Shop
Author: Eth - ethstavern(at)gmail(dot)com
Version: 1.3
Re-Release Date: 01-25-2006
About: An addon for the Equipment Shop that lets you
	   add buffs to existing items. Could be *very*
	   unbalancing. Use at your own risk.
Notes: Inspired by XChrisX's Inventory mod.
	   pieced together from items.php and a few snippets
	   from XChrisX's Inventory System.
Translation compatible. Mostly.
*****************/
function mysticalshop_buffs_getmoduleinfo()
{
    $info = array(
		"name"=>"Equipment Buffs",
		"version"=>"1.42",
		"author"=>"Eth, fixed by Aelia",
		"category"=>"Equipment Shop",
		"download"=>"http://dragonprime.net/users/Eth/mysticalshop_buffs.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Eth/",
		"settings"=>array(
			"togglebuffview"=>"Enable buff previewing?,bool|1",
			"toggleround"=>"Enable item buff rounds description?,bool|1",
			"toggleatkmod"=>"Enable item buff attack bonus description?,bool|1",
			"toggledefmod"=>"Enable item buff defense bonus description?,bool|1",
			"toggledmgmod"=>"Enable item buff damage bonus description?,bool|1",
			"toggleregen"=>"Enable item buff regen description?,bool|1",
			"toggledmgshld"=>"Enable item buff damadgeshield description?,bool|1",
			"togglelifetap"=>"Enable item buff lifetap description?,bool|1",
			"toggleminioncount"=>"Enable item buff minioncount description?,bool|1",
			"togglemaxbadguydamage"=>"Enable item buff max badguy damage description?,bool|0",
			"togglebadguyatkmod"=>"Enable item buff badguy attack bonus description?,bool|0",
			"togglebadguydefmod"=>"Enable item buff badguy defense bonus description?,bool|0",
			"togglebadguydmgmod"=>"Enable item buff badguy damage bonus description?,bool|0",
			"toggleinv"=>"Enable item buff invulnerablility description if true?,bool|0",
		),
		"requires"=>array(
			"mysticalshop"=>"2.8|By Eth, available at Dragonprime.net",
		),
    );

	return $info;
}

function mysticalshop_buffs_install(){
		require_once("modules/mysticalshop_buffs/install.php");
	return true;
}
function mysticalshop_buffs_uninstall(){
	$sql = "DROP TABLE " . DB::prefix("magicitembuffs");
	DB::query($sql);
	return true;
}
function mysticalshop_buffs_dohook($hookname,$args){
	global $session;
	$from = "runmodule.php?module=mysticalshop_buffs&";
	switch($hookname){
	case "newday":
	case "mysticalshop-buy":
		require_once("modules/mysticalshop_buffs/addbuff.php");
		break;
	case "mysticalshop-sell-after":
		require_once("modules/mysticalshop_buffs/stripbuff.php");
		break;
	case "mysticalshop-preview":
		require_once("modules/mysticalshop_buffs/preview.php");
		break;
	case "mysticalshop-editor":
		addnav("Admin Tools");
		addnav("`^Go to Buff Manager",$from."op=editor&what=view");
		break;
	}
	return $args;
}

//code by Thanatos
function mysticalshop_buffs_calc($value){
	global $session;
	$value=preg_replace("/<([A-Za-z0-9]+)\\|([A-Za-z0-9]+)>/","get_module_pref('\\2','\\1')",$value);
	$value=preg_replace("/<([A-Za-z0-9]+)>/","\$session['user']['\\1']",$value);
	eval('$value='.$value.";");
	return $value;
}

function mysticalshop_buffs_run(){
	global $session;
	$title = translate_inline("Equipment Buffs Manager");
	page_header($title);
	$op = httpget('op');
	$id=httpget("id");
	$from = "runmodule.php?module=mysticalshop_buffs&";
	if ($op == "editor"){
		require_once("modules/mysticalshop_buffs/editor.php");
	}
	page_footer();
}
?>
