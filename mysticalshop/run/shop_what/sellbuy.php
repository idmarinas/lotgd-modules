<?php
require_once './modules/mysticalshop/lib.php';

$sellid = httpget('sellid');
$buyid = httpget('buyid');

output('`c`bSell and Buy information`b`c`n');

rawoutput('<big>');
output('Information of item to sell`n`n');
rawoutput('</big>');
//-- Sell information
$cansell = false;
if( is_numeric($sellid) )
{
	$sql = 'SELECT gold,gems,name FROM '.DB::prefix('magicitems').' WHERE id='.$sellid.' LIMIT 1';
	$result = DB::query($sql);

	if( $row = DB::fetch_assoc($result) )
	{
		$gold = $row['gold'];
		$gems = $row['gems'];

		$discount = mysticalshop_applydiscount( $gold, $gems, $disnum );
		$sellgold = round(($gold*.75),0);
		$sellgems = round(($gems*.25),0);
		$gem = translate_inline( 'gem' );
		$gem_pl = translate_inline( 'gems' );

		output("`2%s `2contemplates for a moment, then offers you a deal of `^%s gold `2and `%%s %s `2for your `3%s`2.`n`n", $shopkeep, $sellgold, $sellgems, abs( $sellgems ) != 1 ? $gem_pl : $gem, $row['name'] );
		if( $discount )
			output("`3Thinking that price is much too low, %s`3 reminds you the item is currently being sold at a discounted price, thus your refund is set to match.`n`n", $shopkeep);
		output_notl( '`0' );

		$cansell = true;
	}
	else
	{
		$item_cats = array( 'ring', 'amulet', 'weapon', 'armor', 'cloak', 'helm', 'glove', 'boot', 'misc' );
		output( '`2%s`2 tries to understand what you are trying to sell, but fails to see it. You realize that you only imagined having "%s`2" and feel a little embarrassed.`0`n`n', $shopkeep, get_module_pref( $item_cats[$cat].'name' ) );
		mysticalshop_destroyitem( $item_cats[$cat] );
		addnav( 'Storefront', $from.'op=shop&what=enter' );

		$cansell = false;
	}
}
else output( 'The item can\'t be sold.' );

//-- Buy information
rawoutput('<big>');
output('`nInformation of item to buy`n`n');
rawoutput('</big>');
$canbuy = false;
if( is_numeric( $buyid ) )
{
	$sql = 'SELECT * FROM '.DB::prefix('magicitems').' WHERE id='.$buyid.' LIMIT 1';
	$result = DB::query($sql);
	$row = DB::fetch_assoc($result);
	$what = $row['name'];
	$cat = $row['category'];
	$verbose = $row['bigdesc'];
	$rare = $row['rare'];
	$rarenum = $row['rarenum'];
	$gold = $row['gold'];
	$gems = $row['gems'];

	//display that nice big description you typed out here
	if( $verbose != '' )
	{
		output_notl("`3%s`n`n", $verbose);
	//otherwise, let's display a default one in it's place
	}else{
		output("`3No extended description for this item is available.`n`n");
	}
	if (get_module_setting("showstats")){
		$point = translate_inline( 'point' );
		$points = translate_inline( 'points' );
		$attack = $row['attack'];
		$defense = $row['defense'];
		$charm = $row['charm'];
		$turns = $row['turns'];
		$favor = $row['favor'];
		if ($attack<>0)
			output("`&This item's enchantments will alter your attack by `^%s `&%s.`n", $attack, abs( $attack ) != 1 ? $points : $point );
		if ($defense<>0)
			output("`&This item's enchantments will alter your defense by `^%s `&%s.`n", $defense, abs( $defense ) != 1 ? $points : $point );
		if ($charm<>0)
			output("`&This item's enchantments will alter your charm by `^%s `&%s.`n", $charm, abs( $charm ) != 1 ? $points : $point );
		if ($row['hitpoints']<>0)
			output("`&This item's enchantments will alter your maximum hit points by `^%s`&.`n",$row['hitpoints']);
		if ($turns<>0)
		{
			$stamina = number_format($turns*25000);
			output("`&This item's enchantments will grant `^%s `&extra Stamina points with each day.`n",$stamina);
		}
		if ($favor<>0)
			output( '`&This item\'s unique properties will alter your favour with %s`& by `^%s `&%s.`n', getsetting( 'deathoverlord', '`$Ramius' ), $favor, abs( $favor ) != 1 ? $points : $point );
	}
	//Now let's check if they're buying or selling an item.
	$gem = translate_inline( 'gem' );
	$gem_pl = translate_inline( 'gems' );
	output( '`n`@The cost of %s`@ is `^%s gold`@ and `%%s %s`@.', $what, $gold, $gems, abs( $gems ) != 1 ? $gem_pl : $gem );

	if( mysticalshop_applydiscount( $gold, $gems, $disnum ) )
		output( '`&However, you manage to haggle the price down to `^%s gold`& and `%%s %s`&.', $gold, $gems, abs( $gems ) != 1 ? $gem_pl : $gem );

	//check to see if they can afford it first; saves from having to add extra checks. Bleh.
	$item_categories = ['ring', 'amulet', 'weapon', 'armor', 'cloak', 'helm', 'glove', 'boots', 'misc'];
	if ($session['user']['gold']<$gold or $session['user']['gems']<$gems){
		output("`3However, checking your funds, you realize you can't afford to purchase this item at the moment.");
	//a quick check to make sure there are enough rare items instock for the player
	}else if ($rare == 1 && $rarenum<1){
		output("`n`n`2%s `2suddenly realizes that the item you were about to purchase, `3%s`2, has been sold out.`n`n", $shopkeep,$what);
		output("`2\"Things go fast around here... much too fast sometimes,\" the shopkeeper notes.");
	//otherwise, display the purchase nav
	}else{
		// addnav("Sales");
		// addnav( ['Purchase %s', $what], $from."op=shop&what=purchase&id=$buyid&cat=$cat" );
		$canbuy = true;
	}
	if( $cat == 2 && get_module_setting( 'weapon_atk' ) == 0 )
	{
		output("`n`n`6Be aware that magical weapons are adaptive.");
		output("Precluding any extra magical properties, their attack properties are equal to your level.");
		output("As you grow in strength (gain a level), they do, too.");
	}
	elseif( $cat == 3 && get_module_setting( 'armor_def' ) == 0 )
	{
		output("`n`n`6Be aware that magical armor is adaptive.");
		output("Precluding any extra magical properties, its defensive properties are equal to your level.");
		output("As you grow in strength (gain a level), it does, too.");
	}
	output_notl( '`0`n`n' );
}
else output( 'Nothing to preview.' );

modulehook("mysticalshop-preview", []);

if ($canbuy && $cansell)
{
	addnav('Deal');
	addnav('Accept transaction', $from."op=shop&what=sellbuyfinal&buyid=$buyid&sellid=$sellid&cat=$cat");
}
addnav( 'Merchandise' );
addnav( ['Overview of %s', $names[$cat] ], $from."op=shop&what=viewgoods&cat=$cat" );