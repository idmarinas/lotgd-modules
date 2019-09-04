<?php

function alignmentbuffs_getmoduleinfo()
{
    return [
        'name' => 'Alignment Buffs',
        'version' => '2.0.0',
        'author' => 'SexyCook, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'General',
        'download' => '',
        'settings' => [
            'First Buff,title',
                'activate1' => 'Buff activates on alignment under,int|-2500',
                'buffturns1' => 'Number of turns for the buff,int|500',
                'buffatt1' => 'Attack modifier for the buff (1 equals 100% meaning no change),text|1.3',
                'buffdef1' => 'Defense modifier for the buff (1 equals 100% meaning no change),text|0.85',
            'Second Buff,title',
                'activate2' => 'Buff activates on alignment under x and over that of first buff,int|-1250',
                'buffturns2' => 'Number of turns for the buff,int|250',
                'buffatt2' => 'Attack modifier for the buff (1 equals 100% meaning no change),text|1.2',
                'buffdef2' => 'Defense modifier for the buff (1 equals 100% meaning no change),text|0.9',
            'Third Buff,title',
                'activate3' => 'Buff activates on alignment under x and over that of second buff,int|-625',
                'buffturns3' => 'Number of turns for the buff,int|100',
                'buffatt3' => 'Attack modifier for the buff (1 equals 100% meaning no change),text|1.1',
                'buffdef3' => 'Defense modifier for the buff (1 equals 100% meaning no change),text|0.95',
            'Forth Buff,title',
                'activate4' => 'Buff activates on alignment over x and under that of forth buff,int|625',
                'buffturns4' => 'Number of turns for the buff,int|100',
                'buffatt4' => 'Attack modifier for the buff (1 equals 100% meaning no change),text|0.9',
                'buffdef4' => 'Defense modifier for the buff (1 equals 100% meaning no change),text|1.1',
            'Fifth Buff,title',
                'activate5' => 'Buff activates on alignment over x and under that of fifth buff,int|1250',
                'buffturns5' => 'Number of turns for the buff,int|250',
                'buffatt5' => 'Attack modifier for the buff (1 equals 100% meaning no change),text|0.85',
                'buffdef5' => 'Defense modifier for the buff (1 equals 100% meaning no change),text|1.2',
            'Sixth Buff,title',
                'activate6' => 'Buff activates on alignment over,int|2500',
                'buffturns6' => 'Number of turns for the buff,int|500',
                'buffatt6' => 'Attack modifier for the buff (1 equals 100% meaning no change),text|0.8',
                'buffdef6' => 'Defense modifier for the buff (1 equals 100% meaning no change),text|1.3',
        ],
        'requires' => [
            'alignment' => '>=2.0.0|Alignment Core, Chris Vorndran',
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ],
    ];
}

function alignmentbuffs_install()
{
    module_addhook('newday');

    return true;
}

function alignmentbuffs_uninstall()
{
    return true;
}

function alignmentbuffs_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'newday':

            $alignment = get_module_pref('alignment', 'alignment');

            if ($alignment <= get_module_setting('activate1'))
            {
                $activate = 1;
                $buff = [];
                $buff['name'] = 'buff.name1';
                $buff['rounds'] = get_module_setting('buffturns1');
                $buff['atkmod'] = get_module_setting('buffatt1');
                $buff['defmod'] = get_module_setting('buffdef1');
                $buff['schema'] = 'module-alignmentbuffs';
                apply_buff('alignment', $buff);
            }
            elseif ($alignment <= get_module_setting('activate2'))
            {
                $activate = 2;
                $buff = [];
                $buff['name'] = 'buff.name2';
                $buff['rounds'] = get_module_setting('buffturns2');
                $buff['atkmod'] = get_module_setting('buffatt2');
                $buff['defmod'] = get_module_setting('buffdef2');
                $buff['schema'] = 'module-alignmentbuffs';
                apply_buff('alignment', $buff);
            }
            elseif ($alignment <= get_module_setting('activate3'))
            {
                $activate = 3;
                $buff = [];
                $buff['name'] = 'buff.name3';
                $buff['rounds'] = get_module_setting('buffturns3');
                $buff['atkmod'] = get_module_setting('buffatt3');
                $buff['defmod'] = get_module_setting('buffdef3');
                $buff['schema'] = 'module-alignmentbuffs';
                apply_buff('alignment', $buff);
            }
            elseif ($alignment >= get_module_setting('activate6'))
            {
                $activate = 6;
                $buff = [];
                $buff['name'] = 'buff.name6';
                $buff['rounds'] = get_module_setting('buffturns6');
                $buff['atkmod'] = get_module_setting('buffatt6');
                $buff['defmod'] = get_module_setting('buffdef6');
                $buff['schema'] = 'module-alignmentbuffs';
                apply_buff('alignment', $buff);
            }
            elseif ($alignment >= get_module_setting('activate5'))
            {
                $activate = 5;
                $buff = [];
                $buff['name'] = 'buff.name5';
                $buff['rounds'] = get_module_setting('buffturns5');
                $buff['atkmod'] = get_module_setting('buffatt5');
                $buff['defmod'] = get_module_setting('buffdef5');
                $buff['schema'] = 'module-alignmentbuffs';
                apply_buff('alignment', $buff);
            }
            elseif ($alignment >= get_module_setting('activate4'))
            {
                $activate = 4;
                $buff = [];
                $buff['name'] = 'buff.name4';
                $buff['rounds'] = get_module_setting('buffturns4');
                $buff['atkmod'] = get_module_setting('buffatt4');
                $buff['defmod'] = get_module_setting('buffdef4');
                $buff['schema'] = 'module-alignmentbuffs';
                apply_buff('alignment', $buff);
            }

            if ($activate ?? false)
            {
                $args['includeTemplatesPost']['module/alignmentbuffs/dohook/newday.twig'] = [
                    'textDomain' => 'module-alignmentbuffs',
                    'activate' => $activate
                ];
            }
        break;
    }

    return $args;
}

function alignmentbuffs_run()
{
    global $session;
}
