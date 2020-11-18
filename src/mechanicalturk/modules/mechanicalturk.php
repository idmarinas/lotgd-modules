<?php

function mechanicalturk_getmoduleinfo()
{
    return [
        'name' => 'Mechanical Turk',
        'author' => 'Dan Hall, remodelling/enhancing by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version' => '2.0.0',
        'category' => 'Administrative',
        'download' => '',
        'settings' => [
            'Mechanical Turk Module Settings,title',
            'addpoints' => "How many Donator Points will be awarded when the player's monster is accepted?,int|25",
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition',
        ],
    ];
}

function mechanicalturk_install()
{
    \Doctrine::createSchema([
        'LotgdLocal:ModMechanicalTurk'
    ], true);

    module_addhook('forest');
    module_addhook('superuser');

    return true;
}

function mechanicalturk_uninstall()
{
    \Doctrine::dropSchema([
        'LotgdLocal:ModMechanicalTurk'
    ]);

    return true;
}

function mechanicalturk_dohook($hookname, $args)
{
    global $session, $enemies;

    switch ($hookname)
    {
        case 'forest':
            \LotgdNavigation::addHeader('category.action', ['textDomain' => 'navigation-forest']);
            \LotgdNavigation::addNav('navigation.nav.report', 'runmodule.php?module=mechanicalturk&creatureaction=report', ['textDomain' => 'module-mechanicalturk']);
        break;
        case 'superuser':
            \LotgdNavigation::addHeader('superuser.category.module', ['textDomain' => 'navigation-app']);
            \LotgdNavigation::addNav('navigation.nav.superuser', 'runmodule.php?module=mechanicalturk&creatureaction=showsubmitted', ['textDomain' => 'module-mechanicalturk']);
        break;
        default: break;
    }

    return $args;
}

function mechanicalturk_run()
{
    global $session;

    $op = \LotgdRequest::getQuery('creatureaction');
    $page = (int) \LotgdRequest::getQuery('page');
    $page = max(1, $page);

    $points = get_module_setting('addpoints');
    $textDomain = 'module-mechanicalturk';

    \LotgdResponse::pageStart('title', [], $textDomain);

    $params = [
        'textDomain' => $textDomain,
        'points' => $points
    ];

    $repository = \Doctrine::getRepository('LotgdLocal:ModMechanicalTurk');

    switch ($op)
    {
        case 'reject':
            $params['tpl'] = 'reject';

            $id = \LotgdRequest::getQuery('id');

            $creature = $repository->find($id);

            require_once 'lib/systemmail.php';

            $subject = ['mail.reject.subject', [], $textDomain];
            $body = ['mail.reject.message', ['name' => $creature->getCreaturename()], $textDomain];

            systemmail($creature->getSubmittedbyid(), $subject, $body);

            \Doctrine::remove($creature);

            \Doctrine::flush();

            \LotgdNavigation::addNav('navigation.nav.superuser', 'runmodule.php?module=mechanicalturk&creatureaction=showsubmitted', ['textDomain' => 'module-mechanicalturk']);
        break;
        case 'accept':
            $params['tpl'] = 'accept';

            require_once 'lib/showform.php';

            $id = \LotgdRequest::getQuery('id');

            $creature = $repository->find($id);
            $row = $repository->extractEntity($creature);
            bdump($row, 'Creature ModMechanicalTurk');

            $form = [
                'Creature Properties,title',
                'creaturename' => 'Creature Name',
                'creaturecategory' => 'Creature Category,',
                'creatureweapon' => 'Weapon',
                'creaturewin' => 'Win Message (Displayed when the creature kills the player)',
                'creaturelose' => 'Death Message (Displayed when the creature is killed by the player)',
                'forest' => 'Creature is in Jungle?,bool',
                'graveyard' => 'Creature is on FailBoat?,bool',
                'creaturedescription' => 'A long description of the creature,textarea',
                'notes' => 'Notes on creature analysis,textarea'
            ];

            $params['form'] = lotgd_showform($form, $row, false, false, false);

            require_once 'lib/systemmail.php';

            $subject = ['mail.accept.subject', [], $textDomain];
            $body = ['mail.accept.message', ['name' => $row['creaturename']], $textDomain];

            systemmail($row['submittedbyid'], $subject, $body);

            $accountRepo = \Doctrine::getRepository('LotgdCore:Accounts');
            $entity = $accountRepo->find($row['submittedbyid']);

            if ($entity)
            {
                $entity->setDonation($entity->getDonation() + $points);

                \Doctrine::persist($entity);
            }

            \LotgdNavigation::addNav('navigation.nav.superuser', 'runmodule.php?module=mechanicalturk&creatureaction=showsubmitted', ['textDomain' => 'module-mechanicalturk']);

            debuglog("Add $points donation points as rewards for creature submit.", false, $row['submittedbyid'], 'mechanicalturk');

            \Doctrine::remove($creature);

            \Doctrine::flush();
        break;
        case 'showsubmitted':
            $params['tpl'] = 'showsubmitted';

            $params['paginator'] = $repository->getPaginator($repository->createQueryBuilder('u'), $page);

            \LotgdNavigation::addNav('navigation.nav.return.superuser', 'superuser.php', ['textDomain' => $textDomain]);
            \LotgdNavigation::addNav('navigation.nav.update', 'runmodule.php?module=mechanicalturk&creatureaction=showsubmitted', ['textDomain' => 'module-mechanicalturk']);
        break;
        case 'save':
            $params['tpl'] = 'save';

            $post = \LotgdRequest::getPostAll();

            $entity = $repository->hydrateEntity($post);

            $entity->setSubmittedbyid($session['user']['acctid']);
            $entity->setSubmittedby($session['user']['name']);

            $params['creatureName'] = $post['creaturename'];

            try
            {
                \Doctrine::persist($entity);
                \Doctrine::flush();

                \LotgdFlashMessages::addSuccessMessage(\LotgdTranslator::t('flash.message.save.success', [], $textDomain));
            }
            catch (\Throwable $th)
            {
                \Tracy\Debugger::log($th);

                \LotgdFlashMessages::addErrorMessage(\LotgdTranslator::t('flash.message.save.error', [], $textDomain));
            }

            \LotgdNavigation::addNav('navigation.nav.back', 'forest.php', ['textDomain' => $textDomain]);
        break;
        case 'report':
        default:
            $params['tpl'] = 'default';
            require_once 'lib/showform.php';

            $query = \Doctrine::createQueryBuilder();
            $result = $query->select('u.creaturecategory')
                ->from('LotgdCore:Creatures', 'u')

                ->where('u.creaturecategory != :category')

                ->groupBy('u.creaturecategory')
                ->orderBy('u.creaturecategory', 'ASC')

                ->setParameter('category', '')

                ->getQuery()
                ->getResult()
            ;

            $enum = ',??';

            foreach ($result as $row)
            {
                $enum .= ','.$row['creaturecategory'].','.$row['creaturecategory'];
            }

            $form = [
                'Creature Properties,title',
                'creaturename' => 'Creature Name',
                'creaturecategory' => 'Creature Category,enum,'.$enum,
                'creatureweapon' => 'Weapon',
                'creaturewin' => 'Win Message (Displayed when the creature kills the player)',
                'creaturelose' => 'Death Message (Displayed when the creature is killed by the player)',
                'forest' => 'Creature is in Jungle?,bool',
                'graveyard' => 'Creature is on FailBoat?,bool',
                'creaturedescription' => 'A long description of the creature,textarea',
                'notes' => 'Notes on creature analysis,textarea'
            ];

            \LotgdNavigation::addHeader('navigation.category.return', ['textDomain' => $textDomain]);
            \LotgdNavigation::addNav('navigation.nav.back', 'forest.php', ['textDomain' => $textDomain]);

            $params['form'] = lotgd_showform($form, $row, false, false, false);
        break;
    }

    \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('mechanicalturk/run.twig', $params));

    \LotgdResponse::pageEnd();
}
