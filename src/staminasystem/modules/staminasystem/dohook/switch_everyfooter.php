<?php

if (! $session['user']['loggedin'])
{
    return $args;
}

if (! $session['user']['alive'])
{
    return $args;
}

require_once 'modules/staminasystem/lib/lib.php';
require_once 'lib/redirect.php';

$amber = get_stamina();
$red = get_stamina(0);
$deathOverlord = getsetting('deathoverlord', '`$Ramius`0');

if ($amber < 100 && $red >= 100)
{
    //Gives a proportionate debuff from 1 to 0.2, at 2 decimal places each time
    $buffvalue = round(((($amber / 100) * 80) + 20) / 100, 2);
    $script = '';

    if ($buffvalue < 0.3)
    {
        $buffmsg = 'buff.0.3';
        $message = \LotgdTranslator::t('notify.0.3.message', [], 'module-staminasystem');
        $title = \LotgdTranslator::t('notify.0.3.title', [ 'deathOverlord' => $deathOverlord ], 'module-staminasystem');
        $script = stamina_notification($message, $title);
    }
    elseif ($buffvalue < 0.6)
    {
        $buffmsg = 'buff.0.6';
        $message = \LotgdTranslator::t('notify.0.6.message', [], 'module-staminasystem');
        $title = \LotgdTranslator::t('notify.0.6.title', [ 'deathOverlord' => $deathOverlord ], 'module-staminasystem');
        $script = stamina_notification($message, $title);
    }
    elseif ($buffvalue < 0.8)
    {
        $buffmsg = 'buff.0.8';
        $message = \LotgdTranslator::t('notify.0.8.message', [], 'module-staminasystem');
        $title = \LotgdTranslator::t('notify.0.8.title', [], 'module-staminasystem');
        $script = stamina_notification($message, $title);
    }
    elseif ($buffvalue < 1)
    {
        $buffmsg = 'buff.01';
        $message = \LotgdTranslator::t('notify.01.message', [], 'module-staminasystem');
        $title = \LotgdTranslator::t('notify.01.title', [], 'module-staminasystem');
        $script = stamina_notification($message, $title);
    }

    if ($script)
    {
        apply_buff('stamina-corecombat-exhaustion', [
            'name' => \LotgdTranslator::t('buff.name', [], 'module-staminasystem'),
            'atkmod' => $buffvalue,
            'defmod' => $buffvalue,
            'rounds' => -1,
            'roundmsg' => \LotgdTranslator::t($buffmsg, [], 'module-staminasystem'),
            'schema' => 'module-staminacorecombat'
        ]);

        rawoutput($script);
    }
}
else
{
    strip_buff('stamina-corecombat-exhaustion');
}

if ($red < 100)
{
    $death = e_rand(0, 80);

    if ($death > $red)
    {
        \LotgdFlashMessages::addErrorMessage(\LotgdTranslator::t('flash.message.death', [], 'module-staminasystem'));

        $session['user']['hitpoints'] = 0;
        $session['user']['alive'] = 0;

        return redirect('shades.php');
    }

    $message = \LotgdTranslator::t('notify.red.message', [ 'deathOverlord' => $deathOverlord ], 'module-staminasystem');
    $title = \LotgdTranslator::t('notify.red.title', [], 'module-staminasystem');
    $script = stamina_notification($message, $title, 'danger');

    rawoutput($script);
}

function stamina_notification($message, $title, $status = 'warning')
{
    $message = "$message<br><br><em>".translate_inline('The greater the tiring the greater the penalty to attack and defense.').'</em>';

    return "<script type='text/javascript'>
		Lotgd.notify({
            message : '".addslashes(appoencode($message, true))."',
            title : '".addslashes(appoencode($title, true))."',
			type  : '$status',
			timeOut : 40000,
			escapeHtml : false
		});
	</script>";
}
