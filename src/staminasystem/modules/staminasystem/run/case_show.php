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

$row = [];

foreach ($act as $key => $value)
{
    $keyT = translate($key, 'module-staminasystem');
    $class = translate('' != $value['class'] ? $value['class'] : 'Other', 'module-staminasystem');
    $row[$class][$keyT] = $value;
    $row[$class][$keyT]['levelinfo'] = stamina_level_up($key);
    $row[$class][$keyT]['costwithbuff'] = stamina_calculate_buffed_cost($key);
}

ksort($row);

foreach ($row as &$value)
{
    ksort($value);
}

$params['actions'] = $row;
$params['buffList'] = unserialize(get_module_pref('buffs', 'staminasystem'));

rawoutput(LotgdTheme::renderModuleTemplate('staminasystem/run/show.twig', $params));

popup_footer();
