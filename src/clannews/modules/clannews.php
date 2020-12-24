<?php

// this is a module that should be able to present the recent news items
// of a clan's members in the clan hall.

function clannews_getmoduleinfo()
{
    return [
        'name'     => 'Clan News',
        'category' => 'Clan',
        'author'   => 'dying, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '1.0.0',
        'download' => 'core_module',
        'settings' => [
            'Clan News Settings, title',
            'maxevents' => 'Maximum number of news events to display,range,0,25,1|5',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
        ],
    ];
}

function clannews_install()
{
    module_addhook('clanhall');

    return true;
}

function clannews_uninstall()
{
    return true;
}

function clannews_dohook($hookname, $args)
{
    if ('clanhall' == $hookname)
    {
        global $session, $claninfo;

        $maxEvents      = (int) get_module_setting('maxevents');
        $newsRepository = \Doctrine::getRepository('LotgdCore:News');
        $query          = $newsRepository->createQueryBuilder('u');
        $expr           = $query->expr();

        $query->select('u')
            ->innerJoin('LotgdCore:Accounts', 'a', 'with', $expr->eq('a.acctid', 'u.accountId'))
            ->innerJoin('LotgdCore:Characters', 'c', 'with', $expr->eq('c.id', 'a.character'))
            ->where('c.clanid = :clan')
            ->orderBy('u.date', 'DESC')

            ->setMaxResults($maxEvents)

            ->setParameter('clan', $session['user']['clanid'])
        ;

        $args['templates']['module/clannews/dohook/clanhall.twig'] = [
            'textDomain' => 'module-clannews',
            'name'       => $claninfo['clanname'],
            'result'     => $query->getQuery()->getArrayResult(),
        ];
    }

    return $args;
}

function clannews_run()
{
}
