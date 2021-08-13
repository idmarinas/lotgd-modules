<?php

function onlinelist_getmoduleinfo()
{
    return [
        'name'           => 'Alternative Sorting',
        'author'         => 'Christian Rutsch, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'        => '2.1.0',
        'category'       => 'Administrative',
        'download'       => 'http://dragonprime.net/users/XChrisX/onlinelist.zip',
        'allowanonymous' => true,
        'requires'       => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function onlinelist_install()
{
    module_addhook('onlinecharlist');

    return true;
}

function onlinelist_uninstall()
{
    return true;
}

function onlinelist_dohook($hookname, $args)
{
    switch ($hookname)
    {
        case 'onlinecharlist':
            $args['handled'] = true;

            $repository = \Doctrine::getRepository('LotgdCore:User');
            $query      = $repository->createQueryBuilder('u');
            $expr       = $query->expr();

            //-- Staff users
            $resultStaff = $query->select('c.name')
                ->leftJoin('LotgdCore:Avatar', 'c', 'with', $expr->eq('c.acct', 'u.acctid'))
                ->where('u.locked = 0 AND u.loggedin = 1 AND u.superuser > 0')
                ->orderBy('u.superuser', 'DESC')
                ->addOrderBy('c.level', 'DESC')

                ->getQuery()
                ->getArrayResult()
            ;

            //-- Normal users
            $query         = $repository->createQueryBuilder('u');
            $resultPlayers = $query->select('c.name')
                ->leftJoin('LotgdCore:Avatar', 'c', 'with', $expr->eq('c.acct', 'u.acctid'))
                ->where('u.locked = 0 AND u.loggedin = 1 AND u.superuser = 0')
                ->orderBy('u.superuser', 'DESC')
                ->addOrderBy('c.level', 'DESC')

                ->getQuery()
                ->getArrayResult()
            ;

            $args['list'] = \LotgdTheme::render('@module/onlinelist_onlinecharlist.twig', [
                'staff'      => $resultStaff,
                'players'    => $resultPlayers,
                'textDomain' => 'module_onlinelist',
            ]);
            $args['count'] = \count($resultStaff) + \count($resultPlayers);
        break;
    }

    return $args;
}

function onlinelist_run()
{
}
