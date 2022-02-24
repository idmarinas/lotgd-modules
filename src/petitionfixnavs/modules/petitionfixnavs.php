<?php

use Lotgd\Core\Output\Commentary;
function petitionfixnavs_getmoduleinfo()
{
    return [
        'name'     => 'Fixnavs in petitions',
        'version'  => '2.2.0',
        'author'   => '`2Oliver Brendel, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Administrative',
        'download' => 'http://dragonprime.net/dls/petitionfixnavs.zip',
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function petitionfixnavs_install()
{
    module_addhook_priority('footer-viewpetition');

    return true;
}

function petitionfixnavs_uninstall()
{
    return true;
}

function petitionfixnavs_dohook($hookname, $args)
{
    if ('footer-viewpetition' == $hookname && 'view' == LotgdRequest::getQuery('op'))
    {
        $id = LotgdRequest::getQuery('id');

        LotgdNavigation::addHeader('common.category.navigation', ['textDomain' => 'navigation_app']);

        LotgdNavigation::addNav('navigation.nav.fix', "runmodule.php?module=petitionfixnavs&id={$id}", ['textDomain' => 'module_petitionfixnavs']);
    }

    return $args;
}

function petitionfixnavs_run()
{
    global $session;

    $textDomain = 'module_petitionfixnavs';

    LotgdResponse::pageStart('title', [], $textDomain);

    $id = (int) LotgdRequest::getQuery('id');

    if ($id !== 0)
    {
        $repository = Doctrine::getRepository('LotgdCore:Avatar');
        $query      = Doctrine::createQueryBuilder();

        $accountId = $query->select('u.author')
            ->from('LotgdCore:Petitions', 'u')

            ->where('u.petitionid = :id')

            ->setParameter('id', $id)

            ->getQuery()
            ->getSingleScalarResult()
        ;

        $character = $repository->findBy(['acct' => $accountId]);

        if ($character)
        {
            require_once 'lib/systemmail.php';

            $character->setAllowednavs([])
                ->setSpecialinc('')
            ;

            Doctrine::persist($character);
            Doctrine::flush();

            $commentary = LotgdKernel::get(Commentary::class);
            $commentary->saveComment([
                'section' => "pet-{$id}",
                'comment' => '/me '.LotgdTranslator::t('commentary', [], $textDomain),
            ]);

            systemmail($accountId,
                ['mail.subject', [], $textDomain],
                ['mail.message', ['moderator' => $session['user']['name']], $textDomain]
            );
        }

        return redirect("viewpetition.php?op=view&id={$id}");
    }

    return redirect('viewpetition.php');
}
