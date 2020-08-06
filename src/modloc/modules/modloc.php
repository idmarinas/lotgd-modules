<?php
/*
Details:
 * This is a module for the Grotto
 * It allows you to see which modules have a location setting.
History Log:
    v1.0:
    o Seems to be Stable
    v1.1:
    o Amount counter added
    v1.2.0
    o Update for php >= 5.6
    o Various updates, fixes and optimizations
*/
require_once 'lib/debuglog.php';

function modloc_getmoduleinfo()
{
    return [
        'name' => 'Module Locations',
        'version' => '2.0.0',
        'author' => '`^CortalUX`0, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Administrative',
        'download' => 'core_module',
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function modloc_install()
{
    module_addhook('footer-modules');
    module_addhook('superuser');

    return true;
}

function modloc_uninstall()
{
    return true;
}

function modloc_dohook($hookname, $args)
{
    global $session;

    if (! ($session['user']['superuser'] & SU_MANAGE_MODULES))
    {
        return $args;
    }

    switch ($hookname)
    {
        case 'footer-modules':
            $nav = 'common.category.navigation';
            // no break
        case 'superuser':
            $nav = $nav ?? 'superuser.category.mechanics';
            \LotgdNavigation::addHeader($nav, ['textDomain' => 'navigation-app']);
            \LotgdNavigation::addNav('navigation.nav.locations', 'runmodule.php?module=modloc&admin=true', ['textDomain' => 'module-modloc']);
        break;
        default: break;
    }

    return $args;
}

function modloc_run()
{
    global $session;

    $textDomain = 'module-modloc';

    page_header('title', [], $textDomain);

    $op = \LotgdHttp::getQuery('op');
    $loc = \LotgdHttp::getQuery('loc');

    \LotgdNavigation::superuserGrottoNav();

    \LotgdNavigation::addHeader('navigation.category.locations', ['textDomain' => $textDomain]);
    \LotgdNavigation::addNav('navigation.nav.all', 'runmodule.php?module=modloc&admin=true', ['textDomain' => $textDomain]);
    \LotgdNavigation::addNav('navigation.nav.error', 'runmodule.php?module=modloc&op=error&admin=true', ['textDomain' => $textDomain]);

    \LotgdNavigation::addHeader('navigation.category.filter', ['textDomain' => $textDomain]);

    $params = [
        'textDomain' => $textDomain,
        'location' => $loc
    ];

    $locations = [];
    $t = getsetting('villagename', LOCATION_FIELDS);
    $locations = modulehook('validlocation');
    $locations[$t] = 0;

    foreach ($locations as $name => $sname)
    {
        \LotgdNavigation::addNavNotl($name, 'runmodule.php?module=modloc&admin=true&loc='.urlencode($name));
        $locations[$name] = 0;
    }

    $query = \Doctrine::createQueryBuilder();
    $result = $query->select('u.modulename')
        ->from('LotgdCore:Modules', 'u')

        ->where('u.infokeys LIKE :key')
        ->setParameter('key', '%|settings|%')

        ->getQuery()
        ->getResult()
    ;

    $params['section'] = 'section.run.section.filter';

    if ('' == $loc && '' == $op)
    {
        $params['section'] = 'section.run.section.all';
    }
    elseif ('error' == $op)
    {
        $params['section'] = 'section.run.section.error';
    }

    $params['modules'] = [];
    $params['errors'] = 0;

    foreach ($result as $module)
    {
        $info = get_module_info($module['modulename']);

        if (count($info['settings']))
        {
            foreach ($info['settings'] as $key => $val)
            {
                if (isset($val) && ! empty($val) && isset($key) && ! empty($key))
                {
                    if (is_array($val))
                    {
                        $v = $val[0];
                        $x = explode('|', $v);
                        $val[0] = $x[0];
                        $x[0] = $val;
                    }
                    else
                    {
                        $x = explode('|', $val);
                    }

                    if (! is_array($x[0]))
                    {
                        $type = explode(',', $x[0]);
                    }

                    if (isset($type[1]))
                    {
                        $type = trim($type[1]);
                    }
                    else
                    {
                        $type = 'string';
                    }

                    if ('location' != $type)
                    {
                        continue;
                    }

                    $l = get_module_setting($key, $module['modulename']);

                    if (isset($locations[$l]))
                    {
                        $locations[$l] = ((int) ($locations[$l] ?? 0)) + 1;

                        if ($loc == $l && '' != $loc || '' == $loc && 'error' != $op)
                        {
                            $params['modules'][] = [
                                'error' => false,
                                'location' => $l,
                                'module' => $info['name'],
                                'modulename' => $module['modulename'],
                                'question' => preg_replace('/,location/', '', $x[0])
                            ];
                        }
                    }
                    else
                    {
                        $params['errors']++;

                        if ('' == $loc || 'error' == $op)
                        {
                            $params['modules'][] = [
                                'error' => false,
                                'location' => $l,
                                'module' => $info['name'],
                                'modulename' => $module['modulename'],
                                'question' => preg_replace('/,location/', '', $x[0])
                            ];
                        }
                    }
                }
            }
        }
    }

    $params['modulesCount'] = count($params['modules']);
    $params['locations'] = $locations;

    rawoutput(\LotgdTheme::renderModuleTemplate('modloc/run.twig', $params));

    page_footer();
}
