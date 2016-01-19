<?php 
require_once "modules/staminasystem/lib/lib.php";
require_once("lib/redirect.php");

if (!$session['user']['alive']) return $args;

$amber = get_stamina();
if ($amber < 100)
{	
	$cansado = (!$session['user']['sex']?'cansado':'cansada');
	$exhausto = (!$session['user']['sex']?'exhausto':'exhausta');
	//Gives a proportionate debuff from 1 to 0.2, at 2 decimal places each time
	$buffvalue=round(((($amber/100)*80)+20)/100,2);
	$script = '';
	if ($buffvalue < 0.3)
	{
		$buffmsg = "`\$You're getting `bdangerously exhausted!`b`0";
		if (0 != $amber)
			$script = stamina_notification("Lo dicho, estás peligrosamente $exhausto.", 'La reunión con Ramius está cerca...', 'warning');
	}
	else if ($buffvalue < 0.6)
	{
		$buffmsg = "`\$You're getting `bexhausted!`b`0";
		$script = stamina_notification("... Que no sea por que no te lo advertí, estás $exhausto.", '¿Ganas de visitar a Ramius?', 'warning');
	}
	else if ($buffvalue < 0.8)
	{
		$buffmsg = "`4You're getting `ivery`i tired...`0";
		$script = stamina_notification("Enserio deberías tomarte un descanso, estás muy $cansado.", 'La energía te abandona...', 'warning');
	}
	else if ($buffvalue < 1)
	{
		$buffmsg = "`0You're getting tired...";
		 $script = stamina_notification("Deberías tomarte un descanso, estás un poco $cansado.", 'La energía te abandona.', 'warning');
	}
	
	apply_buff('stamina-corecombat-exhaustion', array(
		"name"=>"Exhaustion",
		"atkmod"=>$buffvalue,
		"defmod"=>$buffvalue,
		"rounds"=>-1,
		"roundmsg"=>$buffmsg,
		"schema"=>"module-staminacorecombat"
	));
    
    rawoutput($script);
} 
else 
{
	strip_buff('stamina-corecombat-exhaustion');
}

$red = get_stamina(0);
if ($red < 100)
{
	$death = e_rand(0,80);
	if ($death > $red){
		output("`\$Vision blurring, you succumb to the effects of exhaustion.  You take a step forward to strike your enemy, but instead trip over your own feet.`nAs the carpet of leaves and twigs drifts lazily up to meet your face, you close your eyes and halfheartedly reach out your hands to cushion the blow - but they sail through the ground as if it were made out of clouds.`nYou fall.`nUnconsciousness.  How you'd missed it.`0");
		$session['user']['hitpoints']=0;
		$session['user']['alive']=0;
			
		redirect("shades.php");
	}
	$script = stamina_notification('Estás en peligro, si continuas con tus acciones corres el riesgo de visitar a Ramius.', 'Ya es tarde para ti', 'danger');
	
	rawoutput($script);
}
// return true;


function stamina_notification($message, $title, $status)
{
	$icon = "<i class='fa fa-exclamation-triangle fa-fw fa-2x'></i>";
	
	$message = "<h6 class='uk-text-left'>$icon $title</h6>$message<br><em>Cuanto mayor sea el cansando mayor será la penalización al ataque y defensa.</em>";
	
	$script = "<script type='text/javascript'>
		UIkit.notify({
			message : '".addslashes($message)."',
			status  : '$status',
			timeout : 5000,
			pos     : 'top-right'
		});
	</script>";	
	
	return $script;	
}