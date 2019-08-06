<?php

require_once 'modules/staminasystem/lib/lib.php';
require_once 'lib/showtabs.php';

popup_header('title.show', [], $textDomain);

$stamina = get_module_pref('stamina');
$daystamina = 2000000;
$redpoint = get_module_pref('red');
$amberpoint = get_module_pref('amber');
$redPct = get_stamina(0);
$amberPct = get_stamina(1);
$greenPct = get_stamina(2);

$color = 'red';
if ($greenPct > 0)
{
    $color = 'green';
}
elseif ($amberPct > 0)
{
    $color = 'orange';
}

$params = [
    'textDomain' => $textDomain,
    'currentStamina' => $stamina,
    'totalStamina' => $daystamina,
    'amberPoint' => $amberpoint,
    'redPoint' => $redpoint,
    'barColor' => $color
];

$act = get_player_action_list();

$layout = [];
$row = [];

foreach ($act as $key => $value)
{
    $class = ('' != $value['class'] ? $value['class'] : 'Other');
    $layout[] = $class;
    $row[$class][$key] = $value;
}

$params['tabs'] = lotgd_showtabs($row, false, 'show_actions');
$params['buffList'] = unserialize(get_module_pref('buffs', 'staminasystem'));

rawoutput(LotgdTheme::renderModuleTemplate('staminasystem/run/show.twig', $params));

popup_footer();

/**
 * Show one item.
 *
 * @param array $act
 *
 * @return void
 */
function show_actions($act)
{
    $action = translate_inline('Action');
    $experience = translate_inline('Experience');
    $cost = translate_inline('Natural Cost');
    $buff = translate_inline('Buff');
    $total = translate_inline('Total');
    $html = "<table class='ui very basic very compact unstackable striped table stamina'><thead><tr><th>$action</th><th>$experience</th><th>$cost</th><th>$buff</th><th>$total</th></tr></thead>";

    ksort($act);

    foreach ($act as $key => $values)
    {
        $lvlinfo = stamina_level_up($key);
        $nextlvlexp = round($lvlinfo['nextlvlexp']);
        $nextlvlexpdisplay = number_format($nextlvlexp);
        $currentlvlexp = round($lvlinfo['currentlvlexp']);
        $cost = $values['naturalcost'];
        $level = $values['lvl'];
        $exp = ($values['exp'] ?? 0);
        $costwithbuff = stamina_calculate_buffed_cost($key);
        $modifier = $costwithbuff - $cost;
        $bonus = 'None';

        if ($modifier < 0)
        {
            $bonus = '`@'.number_format($modifier).'`0';
        }
        elseif ($modifier > 0)
        {
            $bonus = '`$'.number_format($modifier).'`0';
        }

        //current exp - current lvl exp / current exp - nextlvlexp

        $html .= "<tr><td class='collapsing'>".sprintf(translate_inline('`^%s`0 Lv %s'), translate_inline($key), $level).'</td><td>';

        if ($values['lvl'] < 100)
        {
            $expforlvl = $nextlvlexp - $currentlvlexp;
            $expoflvl = $exp - $currentlvlexp;
            $exp = number_format($exp);

            $html .= "<div class='ui tiny indicating progress' data-value='$expoflvl' data-total='$expforlvl'>
				<div class='bar'></div>
				<div class='label'>$exp / $nextlvlexpdisplay</div>
			</div>";
        }
        else
        {
            $html .= '`4`b'.translate_inline('Top Level!').'`b`0';
        }

        $html .= '</td><td>';
        $html .= number_format($cost);
        $html .= '</td><td>';
        $html .= $bonus;
        $html .= '</td><td>';
        $html .= '`Q`b'.number_format($costwithbuff).'`b`0';
        $html .= '</td></tr>';
    }
    $html .= '</table>';

    return appoencode($html, true);
}
