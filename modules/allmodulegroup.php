<?php
//Superuser-Addon
//Anzeigen ALLER installierten Modulen

require_once("lib/superusernav.php");

function allmodulegroup_getmoduleinfo(){
	$info = array(
	"name"=>"All Module Group - Modulemanager",
	"version"=>"0.2",
		"author"=>"`2R`@o`ghe`2n `Qvon `2Fa`@lk`genbr`@uch`0",
		"category"=>"Administrative",
		"download"=>"//www.lotgd.de/downloads/allmodulegroup.zip"
	);
	return $info;
}

function allmodulegroup_install(){
	module_addhook("footer-modules");
	return true;
}

function allmodulegroup_uninstall(){
	return true;
}

function allmodulegroup_dohook($hookname,$args){
	global $session;
	tlschema("allmodulegroup");
	switch($hookname){
		case 'footer-modules':
			if($session['user']['superuser'] & SU_EDIT_COMMENTS) {
				tlschema("modulemanage");
				addnav("Module Categories");
				$sql = "SELECT count(*) as Anzahl FROM " . DB::prefix("modules");
				$result = DB::query($sql);
				$row = $result->current();
				$count=$row['Anzahl'];
				addnav(array("Installierte Module (%s)",$count),"runmodule.php?module=allmodulegroup&op=active");
			}
			break;
	}
	return $args;
}

function allmodulegroup_run()
{
    global $session, $REQUEST_URI;

	tlschema("modulemanage");
	page_header("Module Manager");
	$op = httpget('op');

	superusernav();
	//Module-Liste neu aufbauen
	addnav('Module Categories');
	addnav('', $REQUEST_URI);
	$module = httpget('module');

	if (is_array($module)){
		$modules = $module;
	}else{
		if ($module) $modules = array($module);
		else $modules = array();
	}

	reset($modules);

	$install_status = get_module_install_status();
	$uninstmodules = $install_status['uninstalledmodules'];
	$seencats = $install_status['installedcategories'];
	$ucount = $install_status['uninstcount'];

	ksort($seencats);
	addnav(array(" ?Uninstalled - (%s modules)", $ucount), "modules.php");
	reset($seencats);
	foreach ($seencats as $cat=>$count) {
		addnav(array(" ?%s - (%s modules)", $cat, $count), "modules.php?cat=$cat");
	}
	$sql = "SELECT count(*) as Anzahl FROM " . DB::prefix("modules");
	$result = DB::query($sql);
	$row = DB::fetch_assoc($result);
	$count=$row['Anzahl'];
	addnav(array("Installierte Module (%s)",$count),"runmodule.php?module=allmodulegroup&op=active");


	if ($op=="active") {
	 	//Listen
		output("`$`cAlle Modules`n`c`0");
		$sortby=httpget('sortby');
		if (!$sortby) $sortby="installdate";
		$order=httpget('order');
		$tcat = translate_inline($cat);
		output("`n`b%s Modules`b`n", $tcat);
		$deactivate = translate_inline("Deactivate");
		$activate = translate_inline("Activate");
		$uninstall = translate_inline("Uninstall");
		$reinstall = translate_inline("Reinstall");
		$strsettings = translate_inline("Settings");
		$strnosettings = translate_inline("No Settings");
		$uninstallconfirm = translate_inline("Are you sure you wish to uninstall this module?  All user preferences and module settings will be lost.  If you wish to temporarily remove access to the module, you may simply deactivate it.");
		$status = translate_inline("Status");
		$mname = translate_inline("Module Name");
		$ops = translate_inline("Ops");
		$mauth = translate_inline("Module Author");
		$inon = translate_inline("Installed On");
		$installstr = translate_inline("by %s");
		$active = translate_inline("`@Active`0");
		$inactive = translate_inline("`\$Inactive`0");
        rawoutput("<form action='modules.php?op=mass&cat=$cat' method='POST'>");
		addnav("","modules.php?op=mass&cat=$cat");
		rawoutput("<table class='ui small very compact selectable striped table'>",true);
		rawoutput("<thead><tr><th>&nbsp;</th><th>$ops</th><th><a href='modules.php?cat=$cat&sortby=active&order=".($sortby=="active"?!$order:1)."'>$status</a></th><th><a href='modules.php?cat=$cat&sortby=formalname&order=".($sortby=="formalname"?!$order:1)."'>$mname</a></th><th><a href='modules.php?cat=$cat&sortby=moduleauthor&order=".($sortby=="moduleauthor"?!$order:1)."'>$mauth</a></th><th><a href='modules.php?cat=$cat&sortby=installdate&order=".($sortby=="installdate"?!$order:0)."'>$inon</a></th></thead></tr>");
		addnav("","modules.php?cat=$cat&sortby=active&order=".($sortby=="active"?!$order:1));
		addnav("","modules.php?cat=$cat&sortby=formalname&order=".($sortby=="formalname"?!$order:1));
		addnav("","modules.php?cat=$cat&sortby=moduleauthor&order=".($sortby=="moduleauthor"?!$order:1));
		addnav("","modules.php?cat=$cat&sortby=installdate&order=".($sortby=="installdate"?$order:0));

		$sql = "SELECT * FROM " . DB::prefix("modules") . " ORDER BY active ASC, category ASC";
		$result = DB::query($sql);

        $number = DB::num_rows($result);
        if ($number == 0)
        {
			rawoutput("<tr><td colspan='6' align='center'>");
			output("`i-- No Modules Installed--`i");
			rawoutput("</td></tr>");
		}

        for ($i = 0; $i < $number; $i++)
        {
			$row = DB::fetch_assoc($result);
			rawoutput("<tr>",true);
			rawoutput("<td class='collapsing'>");
			rawoutput("<div class='ui checkbox'><input type='checkbox' name='module[]' value=\"{$row['modulename']}\"></div>");
			rawoutput("</td><td class='collapsing'>");
			if ($row['active']){
				rawoutput("<a data-tooltip='$deactivate' href='modules.php?op=deactivate&module={$row['modulename']}&cat=$cat'>");
				output_notl('<i class="green link power icon"></i>', true);
				rawoutput("</a>");
				addnav("","modules.php?op=deactivate&module={$row['modulename']}&cat=$cat");
			}else{
				rawoutput("<a data-tooltip='$activate' href='modules.php?op=activate&module={$row['modulename']}&cat=$cat'>");
				output_notl('<i class="red link power icon"></i>', true);
				rawoutput("</a>");
				addnav("","modules.php?op=activate&module={$row['modulename']}&cat=$cat");
			}
			rawoutput(" <a data-tooltip='$uninstall' href='modules.php?op=uninstall&module={$row['modulename']}&cat=$cat' onClick='return confirm(\"$uninstallconfirm\");'>");
			output_notl('<i class="red corner remove icon"></i>', true);
			rawoutput("</a>");
			addnav("","modules.php?op=uninstall&module={$row['modulename']}&cat=$cat");
			rawoutput(" <a data-tooltip='$reinstall' href='modules.php?op=reinstall&module={$row['modulename']}&cat=$cat'>");
			output_notl('<i class="orange corner undo icon"></i>', true);
			rawoutput("</a>");
			addnav("","modules.php?op=reinstall&module={$row['modulename']}&cat=$cat");

			if ($session['user']['superuser'] & SU_EDIT_CONFIG) {
				if (strstr($row['infokeys'], "|settings|"))
				{
					rawoutput(" <a data-tooltip='$strsettings' href='configuration.php?op=modulesettings&module={$row['modulename']}'>");
					output_notl('<i class="blue link settings icon"></i>', true);
					rawoutput("</a>");
					addnav("","configuration.php?op=modulesettings&module={$row['modulename']}");
				}
				else
				{
					output_notl(' <span data-tooltip="%s"><i class="red settings icon"></i></span>', $strnosettings, true);
				}
			}

			rawoutput("</td><td>");
			output_notl($row['active']?$active:$inactive);
			require_once("lib/sanitize.php");
			rawoutput("</td><td nowrap><span title=\"".
					(isset($row['description'])&&$row['description']?
					 $row['description']:sanitize($row['formalname']))."\">");
			output_notl("%s", $row['formalname']);
			rawoutput("<br>");
			output_notl("(%s) V%s", $row['modulename'],$row['version']);
			rawoutput("</span></td><td>");
			output_notl("`#%s`0", $row['moduleauthor'], true);
			rawoutput("</td><td nowrap>");
			$line = sprintf($installstr, $row['installedby']);
			output_notl("%s", $row['installdate']);
			rawoutput("<br>");
			output_notl("%s", $line);
			rawoutput("</td></tr>");
		}
		rawoutput("</table><br />");
		$activate = translate_inline("Activate");
		$deactivate = translate_inline("Deactivate");
		$reinstall = translate_inline("Reinstall");
		$uninstall = translate_inline("Uninstall");
		rawoutput("<input type='submit' name='activate' class='button' value='$activate'>");
		rawoutput("<input type='submit' name='deactivate' class='button' value='$deactivate'>");
		rawoutput("<input type='submit' name='reinstall' class='button' value='$reinstall'>");
		rawoutput("<input type='submit' name='uninstall' class='button' value='$uninstall'>");
		rawoutput("</form>");
	}

	page_footer();
}
?>
