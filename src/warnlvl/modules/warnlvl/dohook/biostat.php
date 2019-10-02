<?php

$yomWarning = ($session['user']['superuser'] & SU_GIVES_YOM_WARNING);

if (get_module_setting('show') || $yomWarning)
{
    $id = $args['target']['acctid'];
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
        $args['messages'][] = [
            'bio.warnings',
            [
                'name' => $args['target']['name'],
                'count' => $count,
                'total' => $total
            ],
            'module-warnlvl'
        ];
    }
    elseif (0 == $count && $total > 0)
    {
        $args['messages'][] = [
            'bio.total',
            [
                'name' => $args['target']['name'],
                'total' => $total
            ],
            'module-warnlvl'
        ];
    }
    else
    {
        $args['messages'][] = [
            'bio.none',
            [ 'name' => $args['target']['name'] ],
            'module-warnlvl'
        ];
    }

    if ($count)
    {
        $args['messages'][] = [
            'bio.reason',
            [
                'reasons' => $list2,
                'count' => $count
            ],
            'module-warnlvl'
        ];
    }
}
