<?php

function align($val, $user = false)
{
    $val  = (int) $val;
    $hook = modulehook('align-val-adjust', ['modifier' => 1]);

    $modifier = $hook['modifier'] ?? 1;
    $newval   = \round($val * $modifier, 0);

    increment_module_pref('alignment', $newval, 'alignment', $user);
}

function get_align($user = false)
{
    return get_module_pref('alignment', 'alignment', $user);
}

function set_align($val, $user = false)
{
    set_module_pref('alignment', $val, 'alignment', $user);
}

/**
 * Get align name (code) for user alignment number.
 *
 * @param mixed $user
 */
function get_align_name($user = false)
{
    $evilalign = get_module_setting('evilalign', 'alignment');
    $goodalign = get_module_setting('goodalign', 'alignment');
    $align     = get_align($user);

    if ($goodalign <= $align)
    {
        return 'good';
    }
    elseif ($evilalign >= $align)
    {
        return 'evil';
    }

    return 'neutral';
}
