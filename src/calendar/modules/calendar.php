<?php

// addnews ready
// translator ready
// mail ready

function calendar_getmoduleinfo()
{
    return [
        'name'     => 'Calendar',
        'author'   => 'JT Traub, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '2.2.0',
        'category' => 'General',
        'download' => 'core_module',
        'settings' => [
            'Calendar Settings,title',
            'monthsperyear' => 'How many months per year,range,1,15,1|13',
            'dayspermonth'  => 'How many days per month,range,20,50,1|30',
            'daysperweek'   => 'How many days in a week?,range,1,10,1|8',
            'curMonth'      => 'What month are we in?,range,1,15,1|',
            'curMonthName'  => 'What is the name of current month?,viewonly',
            'curDay'        => 'What is the current day of the month?,range,1,50,1|',
            'curWkday'      => 'What day of the week is it?|',
            'curWkdayName'  => 'What is the name of current weekday?,viewonly',
            'curYear'       => 'What is the current year?,int|',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function calendar_month($month)
{
    switch ($month)
    {
        case 1: return 'Estoran';
        case 2: return 'Kodeturselv';
        case 3: return 'Saliern';
        case 4: return 'Berylin';
        case 5: return 'Umbrolo';
        case 6: return 'Melloch';
        case 7: return 'Morbidan';
        case 8: return 'Verdaege';
        case 9: return 'Amontille';
        case 10: return 'Gwyndri';
        case 11: return 'Sithil';
        case 12: return 'Pentiori';
        case 13: return 'Ornessi';
        case 14: return 'Cresnet';
        case 15: return 'Fueshner';
    }
}

function calendar_weekday($weekday)
{
    switch ($weekday)
    {
        case 1: return 'Erstedaei';
        case 2: return 'Kendaei';
        case 3: return 'Deimsdaei';
        case 4: return 'Jacepdaei';
        case 5: return 'Verdaei';
        case 6: return 'Komodaei';
        case 7: return 'Samodaei';
        case 8: return 'Hennudaei';
        case 9: return 'Boiisdaei';
        case 10: return 'Ghulsdaei';
    }
}

function calendar_install()
{
    module_addhook('newday-runonce');
    module_addhook('newday');
    module_addhook('village-desc');
    module_addhook('changesetting');

    if ( ! get_module_setting('curMonth', 'calendar'))
    {
        $month = \mt_rand(1, 13);
        set_module_setting('curMonth', $month, 'calendar');
        set_module_setting('curMonthName', calendar_month($month), 'calendar');
        set_module_setting('curDay', \mt_rand(1, 30), 'calendar');
        set_module_setting('curYear', \mt_rand(1, 3000), 'calendar');
        $wkday = \mt_rand(1, 8);
        set_module_setting('curWkday', $wkday, 'calendar');
        set_module_setting('curWkdayName', calendar_weekday($wkday), 'calendar');
    }

    return true;
}

function calendar_uninstall()
{
    return true;
}

function calendar_dohook($hookname, $args)
{
    $textDomain = 'module_calendar';

    switch ($hookname)
    {
        case 'changesetting':
            if ('calendar' != $args['module'])
            {
                break;
            }

            if ('curWkday' == $args['setting'])
            {
                set_module_setting('curWkdayName', calendar_weekday($args['new']));
            }
            elseif ('curMonth' == $args['setting'])
            {
                set_module_setting('curMonthName', calendar_month($args['new']));
            }
        break;
        case 'newday-runonce':
            $day   = get_module_setting('curDay');
            $month = get_module_setting('curMonth');
            $year  = get_module_setting('curYear');
            ++$day;

            if ($day > get_module_setting('dayspermonth'))
            {
                $day = 1;
                ++$month;

                if ($month > get_module_setting('monthsperyear'))
                {
                    $month = 1;
                    ++$year;
                }
            }

            set_module_setting('curDay', $day);
            set_module_setting('curMonth', $month);
            set_module_setting('curMonthName', calendar_month($month));
            set_module_setting('curYear', $year);

            $wkday = get_module_setting('curWkday');
            ++$wkday;

            if ($wkday > get_module_setting('daysperweek'))
            {
                $wkday = 1;
            }
            set_module_setting('curWkday', $wkday);
            set_module_setting('curWkdayName', calendar_weekday($wkday));
        break;
        case 'newday':
            $args['includeTemplatesPost']['@module/calendar_newday.twig'] = [
                'textDomain' => $textDomain,
                'wkDayName'  => get_module_setting('curWkdayName'),
                'day'        => get_module_setting('curDay'),
                'monthName'  => get_module_setting('curMonthName'),
                'year'       => get_module_setting('curYear'),
            ];
        break;
        case 'village-desc':
            $args[] = [
                'section.village',
                [
                    'wkDayName' => get_module_setting('curWkdayName'),
                    'day'       => get_module_setting('curDay'),
                    'monthName' => get_module_setting('curMonthName'),
                    'year'      => get_module_setting('curYear'),
                ],
                $textDomain,
            ];
        break;
    }

    return $args;
}

function calendar_run()
{
}
