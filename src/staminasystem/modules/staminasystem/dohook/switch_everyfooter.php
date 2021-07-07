<?php

if ( ! $session['user']['loggedin'])
{
    return $args;
}

if ( ! $session['user']['alive'])
{
    return $args;
}

require_once 'modules/staminasystem/lib/lib.php';
require_once 'lib/redirect.php';

$amber         = get_stamina();
$red           = get_stamina(0);
$deathOverlord = LotgdSetting::getSetting('deathoverlord', '`$Ramius`0');

if ($amber < 100 && $red >= 100)
{
    //Gives a proportionate debuff from 1 to 0.2, at 2 decimal places each time
    $buffvalue = \round(((($amber / 100) * 80) + 20) / 100, 2);
    $script    = '';

    if ($buffvalue < 0.3)
    {
        $buffmsg = 'buff.0.3';
        $message = \LotgdTranslator::t('notify.0.3.message', [], 'module_staminasystem');
        $title   = \LotgdTranslator::t('notify.0.3.title', ['deathOverlord' => $deathOverlord], 'module_staminasystem');
        $script  = stamina_notification($message, $title);
    }
    elseif ($buffvalue < 0.6)
    {
        $buffmsg = 'buff.0.6';
        $message = \LotgdTranslator::t('notify.0.6.message', [], 'module_staminasystem');
        $title   = \LotgdTranslator::t('notify.0.6.title', ['deathOverlord' => $deathOverlord], 'module_staminasystem');
        $script  = stamina_notification($message, $title);
    }
    elseif ($buffvalue < 0.8)
    {
        $buffmsg = 'buff.0.8';
        $message = \LotgdTranslator::t('notify.0.8.message', [], 'module_staminasystem');
        $title   = \LotgdTranslator::t('notify.0.8.title', [], 'module_staminasystem');
        $script  = stamina_notification($message, $title);
    }
    elseif ($buffvalue < 1)
    {
        $buffmsg = 'buff.01';
        $message = \LotgdTranslator::t('notify.01.message', [], 'module_staminasystem');
        $title   = \LotgdTranslator::t('notify.01.title', [], 'module_staminasystem');
        $script  = stamina_notification($message, $title);
    }

    if ($script)
    {
        LotgdKernel::get('lotgd_core.combat.buffs')->applyBuff('stamina-corecombat-exhaustion', [
            'name'     => \LotgdTranslator::t('buff.name', [], 'module_staminasystem'),
            'atkmod'   => $buffvalue,
            'defmod'   => $buffvalue,
            'rounds'   => -1,
            'roundmsg' => \LotgdTranslator::t($buffmsg, [], 'module_staminasystem'),
            'schema'   => 'module-staminacorecombat',
        ]);

        \LotgdResponse::pageAddContent($script);
    }
}
else
{
    LotgdKernel::get('lotgd_core.combat.buffs')->stripBuff('stamina-corecombat-exhaustion');
}

if ($red < 100)
{
    $death = e_rand(0, 80);

    if ($death > $red)
    {
        \LotgdFlashMessages::addErrorMessage(\LotgdTranslator::t('flash.message.death', [], 'module_staminasystem'));

        $session['user']['hitpoints'] = 0;
        $session['user']['alive']     = 0;

        return redirect('shades.php');
    }

    $message = \LotgdTranslator::t('notify.red.message', ['deathOverlord' => $deathOverlord], 'module_staminasystem');
    $title   = \LotgdTranslator::t('notify.red.title', [], 'module_staminasystem');
    $script  = stamina_notification($message, $title, 'danger');

    \LotgdResponse::pageAddContent($script);
}

function stamina_notification($message, $title, $status = 'warning')
{
    $message = "{$message}<br><br><em>".\LotgdTranslator::t('notify.note', [], 'module_staminasystem').'</em>';

    return "<script type='text/javascript'>
		Lotgd.notify({
            message : '".\addslashes(\LotgdFormat::colorize($message, true))."',
            title : '".\addslashes(\LotgdFormat::colorize($title, true))."',
			type  : '{$status}',
			timeOut : 40000,
			escapeHtml : false
		});
	</script>";
}
