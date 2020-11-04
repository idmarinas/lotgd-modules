<?php

function staminahof_getmoduleinfo()
{
    return [
        'name' => 'Stamina System - HOF',
        'version' => '2.0.0',
        'author' => 'Dan Hall, aka Caveman Joe, improbableisland.com, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Stamina',
        'download' => '',
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function staminahof_install()
{
    module_addhook('footer-hof');

    return true;
}

function staminahof_uninstall()
{
    return true;
}

function staminahof_dohook($hookname, $args)
{
    global $session;

    if ('footer-hof' == $hookname)
    {
        \LotgdNavigation::addHeader('category.ranking', ['textDomain' => 'navigation-hof']);
        \LotgdNavigation::addNav('navigation.nav.ranking', 'runmodule.php?module=staminahof', ['textDomain' => 'module-staminahof']);
    }

    return $args;
}

function staminahof_run()
{
    global $session;

    require_once 'modules/staminasystem/lib/lib.php';

    $params = [
        'textDomain' => 'module-staminahof'
    ];

    \LotgdResponse::pageStart('title', [], $params['textDomain']);

    //-- Change text domain for navigation
    \LotgdNavigation::setTextDomain($params['textDomain']);

    \LotgdNavigation::addHeader('navigation.category.exit');
    \LotgdNavigation::addNav('navigation.nav.return', 'hof.php');

    $actions = get_default_action_list();

    \LotgdNavigation::addHeader('navigation.category.actions');
    // Output navs to each action
    foreach ($actions as $action => $vals)
    {
        \LotgdNavigation::addNav($action, 'runmodule.php?module=staminahof&action='.urlencode($action).'&skip=0');
    }

    // Now show the HOF
    $hof = \LotgdHttp::getQuery('action');
    $chof = urlencode($hof);

    $params['tpl'] = 'default';

    if ($hof)
    {
        $params['tpl'] = 'hof';

        $repository = \Doctrine::getRepository('LotgdCore:ModuleUserprefs');
        $query = $repository->createQueryBuilder('u');
        $userActionsArray = $query->select('u.setting', 'u.value', 'u.userid')
            ->addSelect('c.name')
            ->where("u.modulename = 'staminasystem' AND u.setting = 'actions'")
            ->leftJoin('LotgdCore:Characters', 'c', 'with', $query->expr()->eq('c.acct', 'u.userid'))

            ->getQuery()
            ->getArrayResult()
        ;

        $hofPages = [];

        foreach ($userActionsArray as $acctionsArray)
        {
            $acctionsArray['value'] = @unserialize($acctionsArray['value']);

            $hofPages[$acctionsArray['userid']] = $acctionsArray['value'][$hof] ?? [];
            $hofPages[$acctionsArray['userid']]['exp'] = $acctionsArray['value'][$hof]['exp'] ?? 0;
            $hofPages[$acctionsArray['userid']]['lvl'] = $acctionsArray['value'][$hof]['lvl'] ?? 0;
            $hofPages[$acctionsArray['userid']]['name'] = $acctionsArray['name'] ?? '';
            $hofPages[$acctionsArray['userid']]['userid'] = $acctionsArray['userid'] ?? '';
        }

        $actionsPerPage = 20;
        $pages = ceil(count($userActionsArray) / $actionsPerPage);

        \LotgdNavigation::addHeader('navigation.category.pages');

        for ($i = 0; $i < $pages; $i++)
        {
            $page = $i * $actionsPerPage;
            \LotgdNavigation::addNav('navigation.nav.page', "runmodule.php?module=staminahof&action={$chof}&skip={$page}", [
                'params' => ['page' => $i + 1]
            ]);
        }

        usort($hofPages, 'staminahof_sort');

        $params['paginator'] = $hofPages;
        $params['skip'] = (int) \LotgdHttp::getQuery('skip');
        $params['actionHof'] = $hof;
        $params['actionsPerPage'] = $actionsPerPage;
    }

    //-- Restore text domain for navigation
    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('staminahof/run.twig', $params));

    \LotgdResponse::pageEnd();
}

function staminahof_sort($x, $y)
{
    if ($x['exp'] == $y['exp'])
    {
        return 0;
    }
    elseif ($x['exp'] < $y['exp'])
    {
        return 1;
    }

    return -1;
}
