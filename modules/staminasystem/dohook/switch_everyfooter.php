<?php

if (!$session['user']['loggedin']) return $args;
if (!$session['user']['alive']) return $args;

require_once "modules/staminasystem/lib/lib.php";
require_once("lib/redirect.php");

$amber = get_stamina();
if ($amber < 100)
{
	//Gives a proportionate debuff from 1 to 0.2, at 2 decimal places each time
	$buffvalue=round(((($amber/100)*80)+20)/100,2);
	$script = '';
	if ($buffvalue < 0.3)
	{
		$buffmsg = "`\$You're getting `bdangerously exhausted!`b`0";
        $message = translate_inline('That said, you\'re dangerously exhausted.');
        $title = sprintf(translate_inline('The meeting with %s is close...'), getsetting('deathoverlord', '`$Ramius`0'));
		$script = stamina_notification($message, $title, 'warning');
	}
	else if ($buffvalue < 0.6)
	{
		$buffmsg = "`\$You're getting `bexhausted!`b`0";
        $message = translate_inline('...Other than why I did not warn you, you\'re exhausted.');
        $title = sprintf(translate_inline('Do you want to visit %s?'), getsetting('deathoverlord', '`$Ramius`0'));
		$script = stamina_notification($message, $title, 'warning');
	}
	else if ($buffvalue < 0.8)
	{
		$buffmsg = "`4You're getting `ivery`i tired...`0";
        $message = translate_inline('You really should take a break, you\'re very tired.');
        $title = translate_inline('The stamina leaves you...');
		$script = stamina_notification($message, $title, 'warning');
	}
	else if ($buffvalue < 1)
	{
        $buffmsg = "`0You're getting tired...";
        $message = translate_inline('You should take a break, you\'re a little tired.');
        $title = translate_inline('The stamina leaves you...');
		$script = stamina_notification($message, $title, 'warning');
	}

    if ($script)
    {
        apply_buff('stamina-corecombat-exhaustion', [
            'name' => 'Exhaustion',
            'atkmod' => $buffvalue,
            'defmod' => $buffvalue,
            'rounds' => -1,
            'roundmsg' => $buffmsg,
            'schema' => 'module-staminacorecombat'
        ]);

        rawoutput($script);
    }
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
    $message = sprintf(translate_inline('You are in danger, if you continue with your actions you run the risk of visiting %s.'), getsetting('deathoverlord', '`$Ramius`0'));
    $title = translate_inline('It\'s too late for you');
	$script = stamina_notification($message, $title, 'danger');

	rawoutput($script);
}
// return true;


function stamina_notification($message, $title, $status)
{
	$message = "$message<br><br><em>".translate_inline('The greater the tiring the greater the penalty to attack and defense.')."</em>";

	$script = "<script type='text/javascript'>
		Lotgd.notify({
            message : '".addslashes(appoencode($message, true))."',
            title : '".addslashes(appoencode($title, true))."',
			type  : '$status',
			timeOut : 40000,
			escapeHtml : false
		});
	</script>";

	return $script;
}
