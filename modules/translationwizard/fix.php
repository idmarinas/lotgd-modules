<?php
switch ($mode)
{
	case "fix": //remove already translated from the untranslated table (in an extra point because the query takes a few seconds)
		//now clear the untranslated a bit up.... remove all already translated entries to prevent entries for already translated but not deleted ones
		if (get_module_setting("query"))
			{
			$sql= "DELETE from  " . DB::prefix("untranslated") . " where (intext,namespace,language) in (select `intext`, `uri`,`language` FROM " . DB::prefix("translations") . ");"; 						 //works, but only with higher versions of mysql, added in the following query provided by -Torne-
			} else
			{
			$sql= "DELETE from " . DB::prefix("untranslated") . " using " . DB::prefix("untranslated") ." inner join " . DB::prefix("translations") . " on " . DB::prefix("untranslated") .".intext = " . DB::prefix("translations") . ".intext and " . DB::prefix("untranslated") .".namespace = " . DB::prefix("translations") . ".uri and " . DB::prefix("untranslated") .".language = " . DB::prefix("translations") . ".language;";
			}
		$result=DB::query($sql);
		debug("Result back from SQL:".$result->count());
		//done
		output("The untranslated table has been fixed of possible, already translated parts.");
		break;
		default:  //if the user hits the button just to check for duplicates
		$sql= "SELECT u.* FROM " . DB::prefix("untranslated") ." AS u INNER JOIN " . DB::prefix("translations") . " AS t ON u.intext = t.intext AND u.namespace = t.uri AND u.language = t.language;";
		$result=DB::query($sql);
		if (DB::num_rows($result)>0)
			{
			rawoutput("<form action='runmodule.php?module=translationwizard&op=fix&mode=fix' method='post'>");
			addnav("", "runmodule.php?module=translationwizard&op=fix&mode=fix");
			output("`0There are %s entries who already have a translation in the translations table.`n`n",DB::num_rows($result));
			output("`0This operation will delete already translated parts from the untranslated table.`n`n`b`$ This operation can't be made undone!`b`0`n`n");
			rawoutput("<input type='submit' value='". translate_inline("Execute") ."' class='button'>");
			$i = 0;
			output("`n`nFollowing rows are already translated:");
			rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
			rawoutput("<tr class='trhead'><td>". translate_inline("Language") ."</td><td>". translate_inline("Text") ."</td><td>".translate_inline("Module")."</td></tr>");
			while ($row = DB::fetch_assoc($result))
			{
				$i++;
				rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
				rawoutput(htmlentities($row['language'],ENT_COMPAT,$coding));
				rawoutput("</td><td>");
				rawoutput(htmlentities($row['intext'],ENT_COMPAT,$coding));
				rawoutput("</td><td>");
				rawoutput(htmlentities($row['namespace'],ENT_COMPAT,$coding));
				rawoutput("</td></tr>");
			}
					rawoutput("</table>");
			}
			else
			{
			output("Congratulations! Your translation table does not have any redundant entries!");
			}
			rawoutput("</form>");
		break;
	}
?>