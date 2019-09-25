<?php

if (get_module_setting('show') || $yomWarning)
{
    $id = $args['target'];
    $change = false;
    $count = 0;
    $total = 0;
    $list2 = '';
    $allprefs = get_module_pref('allprefs', 'warnlvl', $id);

    if (! empty($allprefs))
    {
        $allprefs = unserialize($allprefs);
        $count = (isset($allprefs['reason'])) ? count($allprefs['reason']) : 0;
        $total = (isset($allprefs['warnings'])) ? $allprefs['warnings'] : 0;

        $reasons = explode("\r\n", get_module_setting('reasons', 'warnlvl'));
        $reasons['999'] = translate_inline('Unknown');
        $keep_days = get_module_setting('days', 'warnlvl');
        $seconds = 60 * 60 * 24 * $keep_days;
        $list = [];

        for ($i = 0; $i < $count; $i++)
        {
            if (0 == $keep_days || ($allprefs['date'][$i] + $seconds) > time())
            {
                $list[$reasons[$allprefs['reason'][$i]]]++;
            }
            else
            {
                unset($allprefs['reason'][$i]);
                unset($allprefs['comments'][$i]);
                unset($allprefs['subber_id'][$i]);
                unset($allprefs['date'][$i]);
                $count--;
            }
        }

        set_module_pref('allprefs', serialize($allprefs), 'warnlvl', $id);

        if (! empty($list))
        {
            foreach ($list as $key => $value)
            {
                if ($value > 1)
                {
                    $list2 .= '`$'.$value.' x '.$key.'`0';
                }
                else
                {
                    $list2 .= '`$'.$key.'`0';
                }
                $list2 .= '`@, ';
            }
            $list2 = rtrim($list2, ', ').'.`0';
        }
    }

    if ($count > 0 && $total > 0)
    {
        output('`^Warnings: `@%s `@currently has `$%s %s`@. %s in total.`n', $args['name'], $count, translate_inline(1 == $count ? 'warning' : 'warnings'), $total);
        $args['messages'][] = [
            'alignment.biostat',
            [ 'align' => ${$align} ],
            'alignment-module'
        ];
    }
    elseif (0 == $count && $total > 0)
    {
        output('`^Warnings: `@%s `@has no current warnings, but has had %s in the past.`n', $args['name'], $total);
        $args['messages'][] = [
            'alignment.biostat',
            [ 'align' => ${$align} ],
            'alignment-module'
        ];
    }
    else
    {
        output('`^Warnings: `@%s `@has had no warnings at all.`n', $args['name']);
        $args['messages'][] = [
            'alignment.biostat',
            [ 'align' => ${$align} ],
            'alignment-module'
        ];
    }

    if (1 == $count)
    {
        output('This warning was for the following reason: %s`n`n', $list2);
        $args['messages'][] = [
            'alignment.biostat',
            [ 'align' => ${$align} ],
            'alignment-module'
        ];
    }
    elseif ($count > 1)
    {
        output('These warnings were for the following reasons: %s`n`n', $list2);
        $args['messages'][] = [
            'alignment.biostat',
            [ 'align' => ${$align} ],
            'alignment-module'
        ];
    }
}
