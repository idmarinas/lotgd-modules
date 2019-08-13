<?php

defined('SU_FIX_BADNAVS') || define('SU_FIX_BADNAVS', SU_MEGAUSER | SU_EDIT_PETITIONS | SU_EDIT_COMMENTS | SU_EDIT_USERS | SU_EDIT_CONFIG | SU_DEVELOPER);

/**
    01/10/09 - v0.0.2
    + Made count number bold, red and blink when more than zero.
*/
function badnavedplayers_getmoduleinfo()
{
    return [
        'name' => 'Badnaved Players',
        'description' => 'Will check for and list players that are stuck in badnav land.',
        'version' => '1.0.0',
        'author' => '`@MarcTheSlayer`0, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Administrative',
        'download' => 'http://dragonprime.net/index.php?topic=10506.0',
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function badnavedplayers_install()
{
    module_addhook('superuser');

    return true;
}

function badnavedplayers_uninstall()
{
    return true;
}

function badnavedplayers_dohook($hookname, $args)
{
    global $session;

    if ('superuser' == $hookname && ($session['user']['superuser'] & SU_FIX_BADNAVS))
    {
        $repository = \Doctrine::getRepository('LotgdCore:Characters');
        $query = $repository->createQueryBuilder('u');
        $count = (int) $query->select('count(u.id)')
            ->where('u.restorepage LIKE :page')

            ->setParameter('page', '%badnav.php%')

            ->getQuery()
            ->getSingleScalarResult()
        ;

        \LotgdNavigation::addHeader('superuser.category.actions', [ 'textDomain' => 'navigation-app' ]);
        \LotgdNavigation::addNav('navigation.nav.superuser', 'runmodule.php?module=badnavedplayers', [
            'textDomain' => 'module-badnavedplayers',
            'params' => [
                'count' => $count
            ]
        ]);
    }

    return $args;
}

function badnavedplayers_run()
{
    global $session;

    $textDomain = 'module-badnavedplayers';

    page_header('title', [], $textDomain);

    $op = \LotgdHttp::getQuery('op');

    $repository = \Doctrine::getRepository('LotgdCore:Characters');

    if ('fix' == $op)
    {
        $playerIds = \LotgdHttp::getPost('fixnav');

        if (is_array($playerIds) && ! empty($playerIds))
        {
            $query = $repository->getQueryBuilder();
            $query->update('LotgdCore:Characters', 'u')
                ->set('u.allowednavs', $query->expr()->literal(''))
                ->set('u.restorepage', $query->expr()->literal(''))
                ->set('u.specialinc', $query->expr()->literal(''))

                ->where('u.id IN (:acct)')

                ->setParameter('acct', $playerIds)

                ->getQuery()
                ->execute()
            ;

            //-- Get account IDs
            $query = $repository->createQueryBuilder('u');
            $result = $query->select('IDENTITY(u.acct) as acct')
                ->where('u.id IN (:acct)')

                ->setParameter('acct', $playerIds)

                ->getQuery()
                ->getArrayResult()
            ;

            //-- Deleted output of accounts
            $query = $repository->getQueryBuilder();
            $query->delete('LotgdCore:AccountsOutput', 'u')
                ->where('u.acctid IN (:acct)')

                ->setParameter('acct', array_map(function ($n) {
                    return $n['acct'];
                }, $result))

                ->getQuery()
                ->execute()
            ;
            \LotgdFlashMessages::addSuccessMessage(\LotgdTranslator::t('flash.message.fixed', [ 'n' => count($playerIds) ], $textDomain));
        }
    }

    $query = $repository->createQueryBuilder('u');
    $result = $query->select('u.id', 'u.name')
        ->where('u.restorepage LIKE :page')

        ->setParameter('page', '%badnav.php%')

        ->getQuery()
        ->getArrayResult()
    ;

    \LotgdNavigation::superuserGrottoNav();

    $params =  [
        'textDomain' => $textDomain,
        'result' => $result
    ];

    rawoutput(\LotgdTheme::renderModuleTemplate('badnavedplayers/run/superuser.twig', $params));

    page_footer();
}
