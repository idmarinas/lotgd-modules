<?php

function alt_char_list_getmoduleinfo()
{
    return [
        'name'     => 'Alternate Character Listing',
        'author'   => 'Chris Vorndran, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '2.1.0',
        'category' => 'Administrative',
        'download' => 'http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1343',
        'prefs'    => [
            'allowed' => 'User allowed to view alts?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function alt_char_list_install()
{
    module_addhook('superuser');
    module_addhook('bioend');

    return true;
}

function alt_char_list_uninstall()
{
    return true;
}

function alt_char_list_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'superuser':
            $allowed = (($session['user']['superuser'] & SU_EDIT_COMMENTS) || get_module_pref('allowed'));

            if ($allowed)
            {
                LotgdNavigation::addHeader('superuser.category.actions', ['textDomain' => 'navigation_app']);
                LotgdNavigation::addNav('navigation.nav.list', 'runmodule.php?module=alt_char_list', ['textDomain' => 'module_alt_char_list']);
            }
        break;
        case 'bioend':
            $allowed = (($session['user']['superuser'] & SU_EDIT_COMMENTS) || get_module_pref('allowed'));

            if ($allowed)
            {
                LotgdNavigation::addHeader('common.superuser.category', ['textDomain' => 'navigation_app']);
                LotgdNavigation::addNav('navigation.nav.view', "runmodule.php?module=alt_char_list&id={{$args['acctid']}&ret=".\urlencode($args['return_link']), ['textDomain' => 'module_alt_char_list']);
            }
        break;
        default: break;
    }

    return $args;
}

function alt_char_list_run()
{
    global $session;

    $textDomain = 'module_alt_char_list';

    LotgdResponse::pageStart('title', [], $textDomain);

    $params = [
        'textDomain' => $textDomain,
    ];

    $op   = (string) LotgdRequest::getQuery('op');
    $id   = (int) LotgdRequest::getQuery('id');
    $page = (int) LotgdRequest::getQuery('page');

    $char = $id;

    LotgdNavigation::setTextDomain($textDomain);

    switch ($op)
    {
        case 'search':
            $params['tpl'] = 'search';

            if (LotgdRequest::isPost())
            {
                $name = (string) LotgdRequest::getPost('char_name');

                $params['search'] = $name;

                $repository = Doctrine::getRepository('LotgdCore:User');
                $query      = $repository->createQueryBuilder('u');

                $query->select('u.acctid', 'u.login', 'u.lastip', 'u.uniqueid', 'u.emailaddress')
                    ->addSelect('c.name')
                    ->leftJoin('LotgdCore:Avatar', 'c', 'with', $query->expr()->eq('c.acct', 'u.acctid'))

                    ->where('c.name LIKE :name OR u.login LIKE :login')

                    ->orderBy('u.acctid', 'ASC')

                    ->setParameter('login', "%{$name}%")
                    ->setParameter('name', "%{$name}%")
                ;

                $params['paginator'] = $repository->getPaginator($query, $page);
            }
        break;
        case '':
        default:
            $params['tpl'] = 'default';

            $repository = Doctrine::getRepository('LotgdCore:User');
            $query      = $repository->createQueryBuilder('u');

            $query->select('u.acctid', 'u.login', 'u.lastip', 'u.uniqueid', 'u.emailaddress')
                ->addSelect('c.name')
                ->leftJoin('LotgdCore:Avatar', 'c', 'with', $query->expr()->eq('c.acct', 'u.acctid'))

                ->orderBy('u.acctid', 'ASC')
            ;
            $subquery = clone $query;

            if ($id !== 0)
            {
                $query->where('u.acctid = :id')
                    ->setParameter('id', $id)
                ;
            }

            $paginator           = $repository->getPaginator($query, $page);
            $params['paginator'] = $paginator;

            $accounts = [];

            foreach ($paginator as $acct)
            {
                $dataIp    = clone $subquery;
                $dataId    = clone $subquery;
                $dataEmail = clone $subquery;

                $dataIp->where('u.lastip = :ip AND u.acctid != :acct')
                    ->setParameter('acct', $acct['acctid'])
                    ->setParameter('ip', $acct['lastip'])
                ;

                $dataId->where('u.uniqueid = :id AND u.acctid != :acct')
                    ->setParameter('acct', $acct['acctid'])
                    ->setParameter('id', $acct['uniqueid'])
                ;

                $dataEmail->where('u.emailaddress = :email AND u.acctid != :acct')
                    ->setParameter('acct', $acct['acctid'])
                    ->setParameter('email', $acct['emailaddress'])
                ;

                $accounts[$acct['acctid']]['acct']  = $acct;
                $accounts[$acct['acctid']]['ip']    = $dataIp->getQuery()->getResult();
                $accounts[$acct['acctid']]['id']    = $dataId->getQuery()->getResult();
                $accounts[$acct['acctid']]['email'] = $dataEmail->getQuery()->getResult();
            }

            $params['accounts'] = $accounts;
        break;
    }

    LotgdNavigation::addHeader('navigation.category.other');
    LotgdNavigation::addNav('navigation.nav.search', 'runmodule.php?module=alt_char_list&op=search');
    LotgdNavigation::addNav('navigation.nav.list', 'runmodule.php?module=alt_char_list');

    $ret = \urlencode(LotgdRequest::getQuery('ret'));

    if ('' != $ret)
    {
        LotgdNavigation::addNav('navigation.nav.return', "bio.php?char={$char}&ret={$ret}");
    }

    LotgdNavigation::superuserGrottoNav();

    LotgdNavigation::setTextDomain();

    LotgdResponse::pageAddContent(LotgdTheme::render('@module/alt_char_list/run.twig', $params));

    LotgdResponse::pageEnd();
}
