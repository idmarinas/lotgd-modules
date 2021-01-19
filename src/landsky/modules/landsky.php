<?php

function landsky_getmoduleinfo()
{
    return [
        'name'      => 'The Sky',
        'version'   => '2.1.0',
        'author'    => '`@CortalUX`&, with modifications by `#Lonnyl`0, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>`0',
        'category'  => 'General',
        'vertxtloc' => 'http://dragonprime.net/users/CortalUX/',
        'download'  => 'http://dragonprime.net/users/CortalUX/landsky.zip',
        'settings'  => [
            'The Sky - General,title',
            'moonBlock' => 'Should the Moons module be blocked during the day?,bool|1',
            'showhome'  => 'Show the Sky on Home Page,bool|1',
        ],
        'requires' => [
            'lotgd' => '>=4.1.0|Need a version equal or greater than 4.1.0 IDMarinas Edition',
        ],
    ];
}

function landsky_install()
{
    module_addhook('everyhit');
    module_addhook('village-desc');
    module_addhook('forest-desc');
    module_addhook('journey-desc');
    module_addhook('page-home-tpl-params');
    module_addhook('page-shades-tpl-params');
    module_addhook('graveyard-desc');

    return true;
}

function landsky_uninstall()
{
    return true;
}

function landsky_dohook($hookname, $args)
{
    global $session;

    $phase = landsky_word();

    if ('everyhit' == $hookname)
    {
        if (1 == get_module_setting('moonBlock') && 0 != $phase && 1 != $phase && 4 != $phase)
        {
            blockmodule('moons');
        }

        return $args;
    }

    $r = ['racedwarf', 'racespecialtyreptile'];

    if ('village-desc' == $hookname)
    {
        foreach ($r as $mod)
        {
            if (is_module_active($mod) && $session['user']['location'] == get_module_setting('villagename', $mod))
            {
                return $args;
            }
        }
    }

    if ( ! \file_exists('public/images/landsky/sky.png'))
    {
        return $args;
    }

    $showhome = get_module_setting('showhome');

    $moons  = modulehook('landsky-moons');
    $params = [
        'textDomain'    => 'module_landsky',
        'landsky_image' => landsky_image(),
        'dimensions'    => landsky_calc(),
        'landsky_c'     => landsky_c(),
        'landsky_word'  => $phase,
        'showMoons'     => ! (1 == get_module_setting('moonBlock') && 0 != $phase && 1 != $phase && 4 != $phase && is_module_active('moons') && 'page-shades-tpl-params' != $hookname && 'graveyard-desc' != $hookname),
        'moons'         => $moons,
        'moonsCount'    => \count($moons),
    ];

    switch ($hookname)
    {
        case 'page-home-tpl-params':
            if ( ! $showhome)
            {
                return $args;
            }

            $args['includeTemplatesIndex']['@module/landsky_sky.twig'] = $params;
        break;
        case 'page-shades-tpl-params':
            $args['includeTemplatesPost']['@module/landsky_sky.twig'] = $params;
        break;
        case 'village-desc':
        case 'forest-desc':
        case 'journey-desc':
        case 'graveyard-desc':
            $args[] = \LotgdTheme::render('@module/landsky_sky.twig', $params);
        break;
    }

    return $args;
}

function landsky_run()
{
}

function landsky_calc()
{
    require_once 'lib/datetime.php';

    $width  = 800;
    $height = 50;

    if (\function_exists('getimagesize') && \file_exists('public/images/landsky/sky.png'))
    {
        $size   = \getimagesize('public/images/landsky/sky.png');
        $width  = $size[0];
        $height = $size[1];
    }

    $width += 50;
    $time = gametimedetails();
    $bit  = $width / 86400;
    $pix  = $bit * $time['secssofartoday'];
    $pix  = \round($pix);

    return ['height' => $height, 'width' => $width, 'offset' => $pix];
}

function landsky_word()
{
    require_once 'lib/datetime.php';

    $num = 0;
    $if  = \date('G', gametime());

    if ($if < 4 || $if >= 19)
    {
        $num = 0;
    }
    elseif ($if >= 4 && $if < 12)
    {
        $num = 1;
    }
    elseif (12 == $if)
    {
        $num = 2;
    }
    elseif ($if > 12 && $if <= 17)
    {
        $num = 3;
    }
    elseif ($if > 15 && $if < 19)
    {
        $num = 4;
    }

    return $num;
}

function landsky_image()
{
    global $session;

    $key = 'deadsky';

    if (landsky_c())
    {
        $key = 'sky';
    }

    return $key;
}

function landsky_c()
{
    global $session;

    if ($session['user']['alive'] || ! $session['user']['loggedin'])
    {
        return true;
    }

    return false;
}
