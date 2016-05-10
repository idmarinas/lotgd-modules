<?php
	page_header("Item Editor");
	require_once("lib/superusernav.php");
	require_once("lib/listfiles.php");
	superusernav();
	addnav("Options - Items");
	addnav("New Item", "runmodule.php?module=inventory&op=editor&op2=newitem");
	addnav("Show all items", "runmodule.php?module=inventory&op=editor&op2=showitems");
	addnav("Options - Buffs");
	addnav("New Buff", "runmodule.php?module=inventory&op=editor&op2=newbuff");
	addnav("Show all buffs", "runmodule.php?module=inventory&op=editor&op2=showbuffs");
	addnav("Other Options");
	require_once("lib/showform.php");
	switch(httpget('op2')) {
		case "newitem2":
			$item = httpallpost();
			$id = httpget('id');
			$value = 0;
			while (list($k,$v)=each($item['activationhook'])){
				if ($v) $value += (int)$k;
			}
			$item['activationhook'] = $value;
			unset($item['showFormTabIndex']);
			require_once("lib/itemhandler.php");
			if (isset($item['itemid']) && $item['itemid'] == 0) unset($item['itemid']);
			inject_item($item);
			invalidatedatacache("item-activation-fightnav-specialties");
			invalidatedatacache("item-activation-forest");
			invalidatedatacache("item-activation-train");
			invalidatedatacache("item-activation-shades");
			invalidatedatacache("item-activation-village");
		case "newitem":
			$id=httpget("id");
			$subop=httpget("subop");
			require_once("lib/itemhandler.php");
			if ($id != "") {
				$item = get_item((int)$id);
				if ($subop=="module") {
					// Save modules settings
					$module = httpget("submodule");
					$post = httpallpost();
					unset($post['showFormTabIndex']);
					reset($post);
					while(list($key, $val) = each($post)) {
						set_module_objpref("items", $id, $key, $val, $module);
						output("`^Saved module objpref %s!`0`n", $key);
					}

				}
				addnav("Item properties", "runmodule.php?module=inventory&op=editor&op2=newitem&id=$id");
				module_editor_navs("prefs-items", "runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id=$id&submodule=");
			}
			if (!is_array($item)) $item = array();
			if ($subop=="module") {
				$module = httpget("submodule");
				rawoutput("<form action='runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id=$id&submodule=$module' method='POST'>");
				module_objpref_edit("items", $module, $id);
				rawoutput("</form>");
				addnav("", "runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id=$id&submodule=$module");
			} else {
				$sql = "SELECT buffid, buffname, buffshortname FROM ".db_prefix("itembuffs");
				$result = db_query($sql);
				while ($row = db_fetch_assoc($result)){
				  $row['buffname'] = str_replace(",", " ", $row['buffname']);
				  $row['buffshortname'] = str_replace(",", " ", $row['buffshortname']);
				  $buffs[] = $row['buffid'];
				  $buffs[] = "{$row['buffname']} ({$row['buffshortname']})";
				}
				if (is_array($buffs) && count($buffs))
					$buffsjoin = "0,none," . join(",",$buffs);
				else
					$buffsjoin = "0,none,";
				$enum_equip=",No where,righthand,Right Hand,lefthand,Left Hand,head,On the Head,body,On Upper Body,arms,On the Arms,legs,On Lower Body,feet,As Shoes,ring,As Ring,neck,Around the Neck,belt,As Belt";
				rawoutput("<form action='runmodule.php?module=inventory&op=editor&op2=newitem2&id=$id' method='post'>");
				addnav("", "runmodule.php?module=inventory&op=editor&op2=newitem2&id=$id");
				$sort = list_files("items",array());
				sort($sort);
				$scriptenum=implode("",$sort);
				$scriptenum=",,none".$scriptenum;
				$format = array(
					"Basic information,title",
						"itemid"=>"Item id,viewhiddenonly",
						"class"=>"Item category, string|Loot",
						"name"=>"Item name, string|",
						"image"=>"Item image (class code for CSS image), string|",
						"description"=>"Description, textarea,60,5|Just a normal, useless item.",
				  	"Values,title",
						"gold"=>"Gold value,int|0",
						"gems"=>"Gem value,int|0",
						"weight"=>"Weight,int|1",
						"droppable"=>"Is this item droppable,bool",
						"level"=>"Minimum level needed,range,1,15,1|1",
						"dragonkills"=>"Dragonkills needed,int|0",
						"customvalue"=>"Custom value for shop,textarea",
						"exectext"=>"Text to display upon activation of the item,string,100",
						"Use %s to insert the item's name!,note",
						"noeffecttext"=>"Text to display if item has no effect,string,100",
						//Se cambia por un sistema de archivos
						// "execvalue"=>"Exec value,textarea",
						"execvalue"=>"Exec value file,enum".$scriptenum,
						"Please see the file 'lib/itemeffects.php' for possible values,note",
						"hide"=>"Hide item from inventory?,bool",
					"Buffs and activation,title",
						"buffid"=>"Activate this buff on useage,enum,$buffsjoin",
						"charges"=>"Amount of charges the item has,int|0",
						"link"=>"Link that's called upon activation,|",
						"activationhook"=>"Hooks which show the item,bitfield,127,"
							.HOOK_NEWDAY.		",Newday,"
							.HOOK_FOREST.		",Forest,"
							.HOOK_VILLAGE.		",Village,"
							.HOOK_SHADES.		",Shades,"
							.HOOK_FIGHTNAV.	",Fightnav,"
							.HOOK_TRAIN.		",Train,"
							.HOOK_INVENTORY.	",Inventory",
					"Chances,title",
						"findrarity"=>"Rarity of object, enum,common,Common,uncommon,Uncommon,rare,Rare,legend,Legend",
						"findchance"=>"Chance to get this item though 'get_random_item()',range,0,100,1|100",
						"loosechance"=>"Chance that this item gets damaged when dying in battle,range,0,100,1|100",
						"dkloosechance"=>"Chance to loose this item after killing the dragon,range,0,100,1|100",
					"Shop Options,title",
						"sellable"=>"Is this item sellable?,bool",
						"buyable"=>"Is this item buyable?,bool",
					"Special Settings,title",
						"uniqueforserver"=>"Is this item unique (server)?,bool",
						"uniqueforplayer"=>"Is this item unique for the player?,bool",
						//"equippable"=>"Is this item equippable?,bool",
						//"equipwhere"=>"Where can this item be equipped?,enum,$enum_equip",
			  );
			  showform($format, $item);
			  rawoutput("</form>");
			}
			break;
		case "takeitem":
			$id = (int)httpget('id');
			add_item($id);
			output("`\$Item no. %s added once, you now have %s pieces.", $id, check_qty($id));
		default:
		case "showitems":
			$sql = "SELECT itemid, class, name, description, gold, gems FROM ".db_prefix("item")." ORDER BY class ASC";
			$result = db_query($sql);
			$edit = translate_inline("Edit");
			$del = translate_inline("Delete");
			$take = translate_inline("Take");
			$conf = translate_inline("Do you really want to delete this item?");
			$oldclass = "";
			for ($i=0;$i<db_num_rows($result);$i++) {
				$row=db_fetch_assoc($result);
				$class = $row['class'];
				if ($class <> $oldclass) output("`n`n`^`b%s`b`0`n", $row['class']);
				$oldclass = $class;
				rawoutput("[ <a href='runmodule.php?module=inventory&op=editor&op2=newitem&id=".$row['itemid']."'>$edit</a> - <a href='runmodule.php?module=inventory&op=editor&op2=delitem&id=".$row['itemid']."' onClick=\"return confirm('$conf');\">$del</a> - <a href='runmodule.php?module=inventory&op=editor&op2=takeitem&id=".$row['itemid']."'>$take</a> ] - ");
				output_notl("`^%s `7- `&`i%s`i `7`n", $row['name'], substr($row['description'],0,47)."...");
				addnav("", "runmodule.php?module=inventory&op=editor&op2=newitem&id=".$row['itemid']);
				addnav("", "runmodule.php?module=inventory&op=editor&op2=delitem&id=".$row['itemid']);
				addnav("", "runmodule.php?module=inventory&op=editor&op2=takeitem&id=".$row['itemid']);
			}
			break;
		case "delitem":
			$id = httpget('id');
			$sql = "DELETE FROM ".db_prefix("item")." WHERE itemid = $id LIMIT 1";
			$result = db_query($sql);
			if (db_affected_rows($result)) output("Item succesfully deleted.`n`n");
			else output("While deleting this item an error occurred. Probably someone has already deleted this item.`n`n");
			$sql = "DELETE FROM ".db_prefix("inventory")." WHERE itemid = $id";
			$result = db_query($sql);
			if (db_affected_rows($result)) output("This item has been removed %s times from players' inventories.`n`n", db_affected_rows($result));
			else output("No item has been deleted from players' inventories.`n`n");
			invalidatedatacache("item-activation-fightnav-specialties");
			invalidatedatacache("item-activation-forest");
			invalidatedatacache("item-activation-train");
			invalidatedatacache("item-activation-shades");
			invalidatedatacache("item-activation-village");

			modulehook('inventory-delete-item', ['id' => $id]);
			break;
		case "newbuff":
			$id=httpget("id");
			$yes = translate_inline("Yes");
			$no = translate_inline("No");
			$buff = [];
			if ($id != "") {
				$sql = "SELECT * FROM ".db_prefix("itembuffs")." WHERE buffid = $id";
				$result = db_query($sql);
				$buff = db_fetch_assoc($result);
			}

			rawoutput("<form action='runmodule.php?module=inventory&op=editor&op2=newbuff2&id=$id' method='post'>");
			addnav("", "runmodule.php?module=inventory&op=editor&op2=newbuff2&id=$id");
			$format = array(
				"General Settings,title",
					'buffid'=>"Buff ID,viewonly",
					'buffname'=>"Buff name (shown in editor),string,250",
					'buffshortname'=>"Buff name (shown in charstats),string,250",
					"The charstats name will automatically use the color of the skill that uses it,note",
					'rounds'=>"Rounds,string,250",
				"Combat Modifiers,title",
					'dmgmod'=>"Damage Modifier (Goodguy),string",
					'atkmod'=>"Attack Modifier (Goodguy),string",
					'defmod'=>"Defense Modifier (Goodguy),string",
					'badguydmgmod'=>"Damage Modifier (Badguy),string",
					'badguyatkmod'=>"Attack Modifier (Badguy),string",
					'badguydefmod'=>"Defense Modifier (Badguy),string",
				"Misc Combat Modifiers,title",
					'lifetap'=>"Lifetap,string,250",
					'damageshield'=>"Damage Shield,string,250",
					'regen'=>"Regeneration,string,250",
				"Minion Count Settings,title",
					'minioncount'=>"Minion count,string,250",
					'minbadguydamage'=>"Min Badguy Damage,string,250",
					'maxbadguydamage'=>"Max Badguy Damage,string,250",
					'mingoodguydamage'=>"Max Goodguy Damage,string,250",
					'maxgoodguydamage'=>"Max Goodguy Damage,string,250",
				"Message Settings,title",
					"You can use %c in any message and it will be replaced with the color code of the skill that activates the buff,note",
					'startmsg'=>"Start Message,string,250",
					'roundmsg'=>"Round Message,string,250",
					'wearoff'=>"Wear Off Message,string,250",
					'effectmsg'=>"Effect Message,string,250",
					'effectfailmsg'=>"Effect Fail Message,string,250",
					'effectnodmgmsg'=>"Effect No Damage Message,string,250",
				"Misc Settings,title",
					'allowinpvp'=>"Allow in PvP?,bool",
					'allowintrain'=>"Allow in Training?,bool",
					'survivenewday'=>"Survive New Day?,bool",
					'invulnerable'=>"Invulnerable?,bool",
					'expireafterfight'=>"Expires after fight?,bool",
			);
			showform($format, $buff);
			rawoutput("</form>");
			break;
		case "newbuff2":
			$post = httpallpost();
			$id = httpget('id');

			unset($post['showFormTabIndex']);

			$post['dmgmod'] = ('' == $post['dmgmod'] ? NULL : $post['dmgmod']);
			$post['badguydmgmod'] = ('' == $post['badguydmgmod'] ? NULL : $post['badguydmgmod']);
			$post['atkmod'] = ('' == $post['atkmod'] ? NULL : $post['atkmod']);
			$post['badguyatkmod'] = ('' == $post['badguyatkmod'] ? NULL : $post['badguyatkmod']);
			$post['defmod'] = ('' == $post['defmod'] ? NULL : $post['defmod']);
			$post['badguydefmod'] = ('' == $post['badguydefmod'] ? NULL : $post['badguydefmod']);

			if (!$id) {
				$insert = DB::insert('itembuffs');
				$insert->values($post);

				DB::execute($insert);

				output("'`^%s`0' inserted.", $post['buffname']);
			} else {
				$update = DB::update('itembuffs');
				$update->set($post)
					->where->equalTo('buffid', $id)
				;

				DB::execute($update);

				invalidatedatacache("inventory-buff-$id");

				output("'`^%s`0' updated.", $post['buffname']);
			}
			break;
		case "showbuffs":
			$sql = "SELECT buffid, buffname, buffshortname FROM ".db_prefix("itembuffs")." ORDER BY buffid ASC";
			$result = db_query($sql);
			$edit = translate_inline("Edit");
			$del = translate_inline("Delete");
			$conf = translate_inline("Do you really want to delete this buff?");
			for ($i=0;$i<db_num_rows($result);$i++) {
				$row=db_fetch_assoc($result);
				output_notl("`^%s `7- `&`i%s`i `7- [", $row['buffname'], $row['buffshortname']);
				rawoutput("<a href='runmodule.php?module=inventory&op=editor&op2=newbuff&id=".$row['buffid']."'>$edit</a> - <a href='runmodule.php?module=inventory&op=editor&op2=delbuff&id=".$row['buffid']."' onClick=\"return confirm('$conf');\">$del</a>");
				addnav("", "runmodule.php?module=inventory&op=editor&op2=newbuff&id=".$row['buffid']);
				addnav("", "runmodule.php?module=inventory&op=editor&op2=delbuff&id=".$row['buffid']);
				output_notl("]`0`n");
			}
			break;
		case "delbuff":
			$id = httpget('id');
			$sql = "DELETE FROM ".db_prefix("itembuffs")." WHERE buffid = $id LIMIT 1";
			$result = db_query($sql);
			if (db_affected_rows($result)) output("Buff succesfully deleted.`n`n");
			else output("While deleting this buffs an error occured. Probably someone else already deleted this buff.`n`n");
	}
	page_footer();
?>