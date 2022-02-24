<?php

function grassyfield_getmoduleinfo()
{
    return [
        'name'     => 'Grassy Field',
        'version'  => '4.0.0',
        'author'   => 'Sean McKillion<br>modified by Eric Stevens & JT Traub, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'requires' => [
            'lotgd' => '>=6.0.0|Need a version equal or greater than 6.0.0 IDMarinas Edition',
        ],
    ];
}

function grassyfield_getrounds()
{
    global $playermount, $session;

    $buff      = $playermount['mountbuff'];
    $maxrounds = $buff['rounds'];
    $cur       = $session['bufflist']['mount']['rounds'] ?? 0;

    return [$maxrounds, $cur];
}

function grassyfield_percent()
{
    global $session;

    $ret = 40;

    if ($session['user']['hashorse'] ?? false)
    {
        list($max, $cur) = grassyfield_getrounds();

        if ($cur > $max * .5)
        {
            $ret = 100;
        }
    }

    return $ret;
}

function grassyfield_install()
{
    module_addeventhook('forest', 'require_once("modules/grassyfield.php"); return grassyfield_percent();');

    return true;
}

function grassyfield_uninstall()
{
    return true;
}

function grassyfield_dohook($hookname, $args)
{
    return $args;
}

function grassyfield_runevent($type)
{
    global $session, $playermount;

    // We assume this event only shows up in the forest currently.
    $from                          = 'forest.php';
    $session['user']['specialinc'] = 'module:grassyfield';

    $op = LotgdRequest::getQuery('op');

    if ('return' == $op)
    {
        $session['user']['specialmisc'] = '';
        $session['user']['specialinc']  = '';

        return redirect($from);
    }

    LotgdKernel::get('lotgd_core.tool.date_time')->checkDay();

    $params = [
        'textDomain'    => 'module_grassyfield',
        'hasHorse'      => (bool) $session['user']['hashorse'],
        'special'       => ('Nothing to see here, move along.' != $session['user']['specialmisc']),
        'staminaSystem' => is_module_active('staminasystem'),
    ];

    LotgdNavigation::addNav('navigation.nav.return', "{$from}?op=return", ['textDomain' => $params['textDomain']]);

    if ($params['special'])
    {
        if ($params['hasHorse'])
        {
            $playermount = LotgdTool::getMount($session['user']['hashorse']);

            $params['mountName'] = $playermount['mountname'] ?? '';
            $params['mount']     = $playermount              ?? [];

            list($max, $cur) = grassyfield_getrounds();

            $params['isPartialRecharge'] = ($cur > $max * .5);

            $buff           = $playermount['mountbuff'];
            $buff['schema'] = $buff['schema'] ?? 'mounts';
            $buff['schema'] = $buff['schema'] ?: 'mounts';

            LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('mount', $buff);

            if ($session['user']['hitpoints'] < $session['user']['maxhitpoints'])
            {
                $params['healed']             = true;
                $session['user']['hitpoints'] = $session['user']['maxhitpoints'];
            }

            $args = [
                'soberval' => 0.8,
                'sobermsg' => LotgdTranslator::t('sober.msg', [], 'module_grassyfield'),
                'schema'   => 'module_grassyfield',
            ];
            modulehook('soberup', $args);

            if ($params['staminaSystem'])
            {
                require_once 'modules/staminasystem/lib/lib.php';
                removestamina(25000);
            }
            else
            {
                --$session['user']['turns'];
            }
        }
        else
        {
            $session['user']['hitpoints'] = \max($session['user']['hitpoints'], $session['user']['maxhitpoints']);
        }

        $session['user']['specialmisc'] = 'Nothing to see here, move along.';
    }

    LotgdResponse::pageAddContent(LotgdTheme::render('@module/grassyfield_runevent.twig', $params));
}

function grassyfield_run()
{
}
