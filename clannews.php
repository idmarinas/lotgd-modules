<?php

// this is a module that should be able to present the recent news items
// of a clan's members in the clan hall.

function clannews_getmoduleinfo()
{
	$info = array(
		"name"=>"Clan News",
		"category"=>"Clan",
		"author"=>"dying",
		"version"=> "0.1",
		"download"=>"core_module",
		"settings"=> array(
			"Clan News Settings, title",
			"maxevents"=>"Maximum number of news events to display,range,0,25,1|5"
		)
	);
   return $info;
}

function clannews_install()
{
   module_addhook("clanhall");

   return true;
}

function clannews_uninstall()
{
   return true;
}

function clannews_dohook($hookname, $args)
{
	switch ($hookname) {
	case "clanhall":
		global $session, $claninfo;

		$maxevents = get_module_setting("maxevents");

		$sql = "SELECT " . DB::prefix("news") . ".* FROM " . DB::prefix("news") . " INNER JOIN " . DB::prefix("accounts")
			. " ON " . DB::prefix("news") . ".accountid = " . DB::prefix("accounts") . ".acctid"
			. " WHERE " . DB::prefix("accounts") . ".clanid = " . $session['user']['clanid']
			. " ORDER BY " . DB::prefix("news") . ".newsid DESC LIMIT " . $maxevents;
		$res = DB::query($sql);

		if (DB::num_rows($res)) {
			output("`n`n`b`&Recent News for %s:`b`0`n", $claninfo['clanname']);
			rawoutput("<ul type='square'>");

			tlschema("news");

			for ($i=0; $i<DB::num_rows($res); $i++) {
				$row = DB::fetch_assoc($res);
				tlschema($row['tlschema']);
				if ($row['arguments'] != "") {
					$args = unserialize($row['arguments']);
					array_unshift($args, $row['newstext']);
					$news = call_user_func_array("sprintf_translate", $args);
				} else {
					$news = translate_inline($row['newstext']);
				}
				tlschema();
				if ($i!=0) clannews_outputseparator();
				rawoutput("<li>");
				output_notl("`@$news`0");
			}
			rawoutput("</ul>");

			tlschema();
		}
		break;
	}
	return $args;
}

function clannews_outputseparator()
{
	// the line below is the output used to separate news events in
	// news.php.  however, it doesn't work well with the page layout
	// of clan.php, since it takes up a bit more vertical space than
	// the style of the other elements on the page does.
	// output_notl("`c`2-=-`@=-=`2-=-`@=-=`2-=-`@=-=`2-=-`0`c");
	rawoutput("<table cellspacing=0><tr><td height=5></td></tr></table>");
}

?>
