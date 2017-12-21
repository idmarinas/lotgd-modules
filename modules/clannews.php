<?php

// this is a module that should be able to present the recent news items
// of a clan's members in the clan hall.

function clannews_getmoduleinfo()
{
	return [
		'name' => 'Clan News',
		'category' => 'Clan',
		'author' => 'dying',
		'version' => '0.2.0',
		'download' => 'core_module',
		'settings' => [
			'Clan News Settings, title',
			'maxevents'=>'Maximum number of news events to display,range,0,25,1|5'
        ]
    ];
}

function clannews_install()
{
   module_addhook('clanhall');

   return true;
}

function clannews_uninstall() { return true; }

function clannews_dohook($hookname, $args)
{
	if ('clanhall' == $hookname)
    {
		global $session, $claninfo;

		$maxevents = get_module_setting("maxevents");

		$sql = "SELECT " . DB::prefix("news") . ".* FROM " . DB::prefix("news") . " INNER JOIN " . DB::prefix("accounts")
			. " ON " . DB::prefix("news") . ".accountid = " . DB::prefix("accounts") . ".acctid"
			. " WHERE " . DB::prefix("accounts") . ".clanid = " . $session['user']['clanid']
			. " ORDER BY " . DB::prefix("news") . ".newsid DESC LIMIT " . $maxevents;
		$res = DB::query_cached($sql, "clan-news-{$session['user']['clanid']}");

        if (! DB::num_rows($res)) return $args;

		output("`n`n`b`&Recent News for %s:`b`0`n", $claninfo['clanname']);
		rawoutput("<div class='ui segment'><ul class='ui list'>");
		tlschema("news");
		foreach ($res as $key => $row)
        {
			tlschema($row['tlschema']);
			if ($row['arguments'] != "")
            {
				$args = unserialize($row['arguments']);
				array_unshift($args, $row['newstext']);
				$news = call_user_func_array("sprintf_translate", $args);
			}
            else $news = translate_inline($row['newstext']);
			tlschema();
			rawoutput("<li>");
			output_notl("`@$news`0");
		}
		rawoutput("</ul></div>");

		tlschema();
	}

	return $args;
}

function clannews_run() {}
