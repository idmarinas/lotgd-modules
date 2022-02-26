<?php

use Lotgd\Core\Character\Stats;
global $badguy, $actions_used;

//Look at the number of Turns we're missing.  Default is ten, and we'll add or remove some Stamina depending, as long as we're not in a fight.
if (0 != get_module_setting('turns_emulation_base') && ! isset($badguy))
{
    $stamina = e_rand(get_module_setting('turns_emulation_base'), get_module_setting('turns_emulation_ceiling'));

    while ($session['user']['turns'] < 10)
    {
        ++$session['user']['turns'];
        LotgdResponse::pageDebug('Turns Removed');
        removestamina($stamina);
    }

    while ($session['user']['turns'] > 10)
    {
        --$session['user']['turns'];
        LotgdResponse::pageDebug('Turns Added');
        addstamina($stamina);
    }
}

$stats         = LotgdKernel::get(Stats::class);
$charstat_info = $stats->getStats();

$actionRecentTitle = LotgdTranslator::t('statistic.category.action.recent', [], 'app_default');
$actionRankTitle   = LotgdTranslator::t('statistic.category.action.rank', [], 'app_default');

//add recent actions to the _top_ of the charstat column
if ( ! isset($charstat_info[$actionRecentTitle]))
{
    //Put yer thing down, flip it an' reverse it
    $yarr                     = \array_reverse($charstat_info);
    $yarr[$actionRankTitle]   = [];
    $yarr[$actionRecentTitle] = [];
    $charstat_info            = \array_reverse($yarr);

    $stats->setStats($charstat_info);
}

if (isset($actions_used))
{
    foreach ($actions_used as $action => $vals)
    {
        if ( ! $actions_used[$action]['lvlinfo']['currentlvlexp'])
        {
            $actions_used[$action]['lvlinfo']['currentlvlexp'] = 1;
        }
        $pct = (($actions_used[$action]['lvlinfo']['exp'] - $actions_used[$action]['lvlinfo']['currentlvlexp']) / ($actions_used[$action]['lvlinfo']['nextlvlexp'] - $actions_used[$action]['lvlinfo']['currentlvlexp'])) * 100;

        $disp = "<div class='ui lotgd tiny indicating progress staminasystem action' data-percent='{$pct}'>
			<div class='bar'></div>
            <div class='label'>".LotgdTranslator::t('charstats.action_used',
                [
                    'lvl' => $actions_used[$action]['lvlinfo']['lvl'],
                    'exp' => $actions_used[$action]['exp_earned'],
                ],
                'module_staminasystem'
            ).'</div>
		</div>';
        LotgdKernel::get("Lotgd\Core\Character\Stats")->setcharstat($actionRecentTitle, $action, $disp);

        if (get_module_pref('user_minihof'))
        {
            $st = \microtime(true);
            stamina_minihof($action);
            $en = \microtime(true);
            $to = $en - $st;
            LotgdResponse::pageDebug('Minihof: '.$to);
        }
    }
}

//Values
$stamina    = get_module_pref('stamina');
$daystamina = 2000000;
$redpct     = get_stamina(0);
$amberpct   = get_stamina(1);
$greenpct   = get_stamina(2);

//Then, since Turns are pretty well baked into core and we don't want to be playing around with adding turns just as they're needed for core to operate, we'll just add ten turns here and forget all about it...
$session['user']['turns'] = 10;

if ( ! $redpct)
{
    $session['user']['gravefights'] = 0;
    $session['user']['turns']       = 0;
}

//Display the actual Stamina bar
$pctoftotal = \round($stamina / $daystamina * 100, 5);

$color = 'red';

if ($greenpct > 0)
{
    $color = 'green';
}
elseif ($amberpct > 0)
{
    $color = 'orange';
}

$alert = '';

if ( ! $session['user']['dragonkills'] && $session['user']['age'] <= 1 && $greenpct <= 1)
{
    $alert = '- '.LotgdTranslator::t('section.charstats.alert', [], 'module_staminasystem');
}

$new = "<a id='module-staminasystem-show' href='' onclick=\"JaxonLotgd.Ajax.Local.ModuleStaminaSystem.show(this); $(this).addClass('loading disabled'); return false;\"><div data-content='{$pctoftotal}% {$alert}' class='ui lotgd tooltip tiny progress remove margin {$color} staminasystem staminabar' data-value='{$stamina}' data-total='{$daystamina}'><div class='bar'></div></div></a>";

LotgdKernel::get("Lotgd\Core\Character\Stats")->setcharstat(
    LotgdTranslator::t('statistic.category.character.info', [], 'app_default'),
    LotgdTranslator::t('charstats.stat', [], 'module_staminasystem'),
    $new
);

LotgdNavigation::addNavAllow('', 'runmodule.php?module=staminasystem&op=show');
