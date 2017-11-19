<?php
output("Overview:");
output_notl("`n`n");
$sql= "SELECT count(  tid  )  AS counter, uri,language FROM ".DB::prefix("translations")." GROUP  BY BINARY language,uri;";
$result = DB::query($sql);
rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
rawoutput("<tr class='trhead'><td>". translate_inline("Language") ."</td><td>". translate_inline("Namespace") ."</td><td>".translate_inline("# of rows")."</td></tr>");
output("`bYour translations table has the following structure:`b");
output_notl("`n`n");
	if (DB::num_rows($result)>0)
		{
		while($row=DB::fetch_assoc($result))
			{
			rawoutput("<tr>");
			rawoutput("<td>");
			rawoutput(htmlentities($row['language'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['uri'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['counter'],ENT_COMPAT,$coding));
			rawoutput("</td></tr>");
			}
		}
		rawoutput("</table>");
output_notl("`n`n");
$sql= "SELECT count(  tid  )  AS counter, language FROM ".DB::prefix("translations")." GROUP  BY BINARY language HAVING counter >1;";
$result = DB::query($sql);
rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
rawoutput("<tr class='trhead'><td>". translate_inline("Language") ."</td><td>".translate_inline("# of rows")."</td></tr>");
	if (DB::num_rows($result)>0)
		{
		while($row=DB::fetch_assoc($result))
			{
			rawoutput("<tr>");
			rawoutput("<td>");
			rawoutput(htmlentities($row['language'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['counter'],ENT_COMPAT,$coding));
			rawoutput("</td></tr>");
			}
		}
		rawoutput("</table>");
output_notl("`n`n");
output("`bYour untranslated table has the following structure:`b");
output_notl("`n`n");
$sql= "SELECT count(  intext  )  AS counter, namespace,language FROM ".DB::prefix("untranslated")." GROUP  BY BINARY language,namespace;";
$result = DB::query($sql);
rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
rawoutput("<tr class='trhead'><td>". translate_inline("Language") ."</td><td>". translate_inline("Namespace") ."</td><td>".translate_inline("# of rows")."</td></tr>");
	if (DB::num_rows($result)>0)
		{
		while($row=DB::fetch_assoc($result))
			{
			rawoutput("<tr>");
			rawoutput("<td>");
			rawoutput(htmlentities($row['language'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['namespace'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['counter'],ENT_COMPAT,$coding));
			rawoutput("</td></tr>");
			}
		}
		rawoutput("</table>");
output_notl("`n`n");
$sql= "SELECT count(  intext  )  AS counter, language FROM ".DB::prefix("untranslated")." GROUP  BY BINARY language;";
$result = DB::query($sql);
rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
rawoutput("<tr class='trhead'><td>". translate_inline("Language") ."</td><td>".translate_inline("# of rows")."</td></tr>");
	if (DB::num_rows($result)>0)
		{
		while($row=DB::fetch_assoc($result))
			{
			rawoutput("<tr>");
			rawoutput("<td>");
			rawoutput(htmlentities($row['language'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['counter'],ENT_COMPAT,$coding));
			rawoutput("</td></tr>");
			}
		}
		rawoutput("</table>");
output_notl("`n`n");
output("`bYour pulled translations table has the following structure:`b");
output_notl("`n`n");
$sql= "SELECT count(  *  )  AS counter, uri,language FROM ".DB::prefix("temp_translations")." GROUP  BY BINARY language,uri;";
$result = DB::query($sql);
rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
rawoutput("<tr class='trhead'><td>". translate_inline("Language") ."</td><td>". translate_inline("Namespace") ."</td><td>".translate_inline("# of rows")."</td></tr>");
	if (DB::num_rows($result)>0)
		{
		while($row=DB::fetch_assoc($result))
			{
			rawoutput("<tr>");
			rawoutput("<td>");
			rawoutput(htmlentities($row['language'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['uri'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['counter'],ENT_COMPAT,$coding));
			rawoutput("</td></tr>");
			}
		}
		rawoutput("</table>");
output_notl("`n`n");
$sql= "SELECT count(  intext  )  AS counter, language FROM ".DB::prefix("temp_translations")." GROUP  BY BINARY language;";
$result = DB::query($sql);
rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
rawoutput("<tr class='trhead'><td>". translate_inline("Language") ."</td><td>".translate_inline("# of rows")."</td></tr>");
	if (DB::num_rows($result)>0)
		{
		while($row=DB::fetch_assoc($result))
			{
			rawoutput("<tr>");
			rawoutput("<td>");
			rawoutput(htmlentities($row['language'],ENT_COMPAT,$coding));
			rawoutput("</td><td>");
			rawoutput(htmlentities($row['counter'],ENT_COMPAT,$coding));
			rawoutput("</td></tr>");
			}
		}
		rawoutput("</table>");
