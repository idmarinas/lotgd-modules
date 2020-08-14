<?php

function cityprefs_getmoduleinfo()
{
    return [
        'name' => 'City Preferences Addon',
        'version' => '3.0.0',
        'author' => 'Sixf00t4, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'General',
        'description' => 'Gives the ability to use prefs based on cities',
        'vertxtloc' => 'http://www.legendofsix.com/',
        'download' => 'http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1155',
        'settings' => [
            'data_imported' => 'Imported old data,viewonly',
        ],
        'requires' => [
            'lotgd' => '>=4.3.0|Need a version equal or greater than 4.3.0 IDMarinas Edition',
            'cities' => '>=2.0.0|Multiple Cities - Core module'
        ]
    ];
}

function cityprefs_install()
{
    global $session;

    if (\Doctrine::createSchema(['LotgdLocal:ModuleCityprefs'], true))
    {
        if ($session['user']['superuser'] & ~SU_DOESNT_GIVE_GROTTO)
        {
            debug('Installing cityprefs Module.');
        }

        if (! get_module_setting('data_imported', 1, 'cityprefs'))
        {
            $page = 1;
            $select = \DB::select('cityprefs');
            $paginator = \DB::paginator($select, $page, 100);
            $hydrator = new \Zend\Hydrator\ClassMethods();

            $pageCount = $paginator->count();
            $importCount = $paginator->getTotalItemCount();

            //-- Overrides the automatic generation of IDs
            $metaData = \Doctrine::getClassMetadata('LotgdLocal:ModuleCityprefs');
            $metaData->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
            $metaData->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

            do
            {
                foreach ($paginator as $row)
                {
                    $row = (array) $row;
                    $row['id'] = $row['cityid'];

                    $entity = $hydrator->hydrate($row, new \Lotgd\Local\Entity\ModuleCityprefs());

                    \Doctrine::persist($entity);
                }

                \Doctrine::flush();

                $page++;
                $paginator = \DB::paginator($select, $page, 100);
            } while ($paginator->getCurrentItemCount() && $page <= $pageCount);

            //-- Restore the automatic generation of IDs
            $metaData->setIdGenerator(new \Doctrine\ORM\Id\IdentityGenerator());
            $metaData->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_IDENTITY);

            \LotgdFlashMessages::addInfoMessage(sprintf('Import %s rows from "cityprefs" to "module_cityprefs" table', $importCount));

            set_module_setting('data_imported', 1, 'cityprefs');
        }

        $repository = \Doctrine::getRepository('LotgdLocal:ModuleCityprefs');

        $vloc = [];
        $vloc = modulehook('validlocation', $vloc);
        ksort($vloc);
        reset($vloc);

        //-- Install/Update capital city
        $capital = $repository->findOneBy(['module' => 'none']);
        $capital = $repository->hydrateEntity([
            'module' => 'none',
            'cityName' => getsetting('villagename', LOCATION_FIELDS)
        ], $capital);

        \Doctrine::persist($capital);

        //-- Install/Update cities
        $query = $repository->getQueryBuilder();
        $result = $query->select('u')
            ->from('LotgdCore:ModuleSettings', 'u')
            ->where('u.value IN (:loc) AND u.setting = :set')

            ->setParameter('loc', \array_keys($vloc))
            ->setParameter('set', 'villagename')

            ->getQuery()
            ->getResult()
        ;

        foreach ($result as $value)
        {
            $entity = $repository->findOneBy(['module' => $value->getModulename()]);
            $entity = $repository->hydrateEntity([
                'module' => $value->getModulename(),
                'cityName' => $value->getValue()
            ], $entity);

            \Doctrine::persist($entity);
        }

        \Doctrine::flush();
    }
    else
    {
        if ($session['user']['superuser'] & ~SU_DOESNT_GIVE_GROTTO)
        {
            debug('Updating cityprefs Module.');
        }
    }

    module_addhook('superuser');
    module_addhook('changesetting');

    return true;
}

function cityprefs_uninstall()
{
    debug('Un-Installing cityprefs Module.');
    \Doctrine::dropSchema(['LotgdLocal:ModuleCityprefs']);

    debug('Dropping objprefs related to cities');
    $objRepository = \Doctrine::getRepository('LotgdCore:ModuleObjprefs');
    //-- Updated location
    $query = $objRepository->getQueryBuilder();
    $query->delete('LotgdCore:ModuleObjprefs', 'u')
        ->where('u.objtype = :old')

        ->setParameter('old', 'city')

        ->getQuery()
        ->execute()
    ;

    return true;
}

function cityprefs_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'changesetting':
            if ('villagename' == $args['setting'])
            {
                $repository = \Doctrine::getRepository('LotgdLocal:ModuleCityprefs');
                $entity = $repository->findOneBy(['cityName' => $args['old']]);

                if ($entity)
                {
                    $entity = $repository->hydrateEntity([
                        'cityName' => $args['new']
                    ], $entity);

                    \Doctrine::persist($entity);
                    \Doctrine::flush();
                }
            }
        break;
        case 'superuser':
            if ($session['user']['superuser'] & SU_EDIT_USERS)
            {
                \LotgdNavigation::addHeader('superuser.category.editors', ['textDomain' => 'navigation-app']);
                \LotgdNavigation::addNav('navigation.nav.editor', 'runmodule.php?module=cityprefs&op=su', ['textDomain' => 'module-cityprefs']);
            }
        break;
    }

    return $args;
}

function cityprefs_run()
{
    global $session;

    $textDomain = 'module-cityprefs';

    page_header('title.default', [], $textDomain);

    $repository = \Doctrine::getRepository('LotgdLocal:ModuleCityprefs');

    $op = (string) \LotgdHttp::getQuery('op');
    $cityId = (int) \LotgdHttp::getQuery('cityid');
    $mdule = (string) \LotgdHttp::getQuery('mdule');

    //-- Change text domain for navigation
    \LotgdNavigation::setTextDomain($textDomain);

    if ($cityId)
    {
        $cityName = $repository->getCityNameById($cityId);

        page_header('title.properties', ['city' => $cityName], $textDomain);

        $modu = $repository->getModuleNameByCityId($cityId);

        if ('none' != $modu)
        {
            \LotgdNavigation::addHeader('navigation.category.operations');
            \LotgdNavigation::addNav('navigation.nav.modsettings', "configuration.php?op=modulesettings&module=$modu");
        }

        \LotgdNavigation::addHeader('common.category.navigation', ['textDomain' => 'navigation-app']);

        $link = 'village.php';

        if (is_module_active('cities'))
        {
            $link = 'runmodule.php?module=cities&op=travel&city='.urlencode($cityName).'&su=1';
        }

        \LotgdNavigation::addNav('navigation.nav.journey', $link, [
            'params' => ['city' => $cityName]
        ]);
    }

    \LotgdNavigation::superuserGrottoNav();

    if (is_module_active('modloc'))
    {
        \LotgdNavigation::addNav('navigation.nav.modloc', 'runmodule.php?module=modloc');
    }

    if ('su' != $op)
    {
        \LotgdNavigation::addNav('navigation.nav.back.list', 'runmodule.php?module=cityprefs&op=su');
    }

    $params = [
        'textDomain' => $textDomain
    ];

    switch ($op)
    {
        case 'su':
            $params['tpl'] = 'default';
            \LotgdNavigation::addHeader('navigation.category.operations');
            \LotgdNavigation::addNav('navigation.nav.autoadd', 'runmodule.php?module=cityprefs&op=update');

            $params['paginator'] = $repository->findAll();
        break;

        case 'update':
            $repository = \Doctrine::getRepository('LotgdLocal:ModuleCityprefs');

            $vloc = [];
            $vloc = modulehook('validlocation', $vloc);
            ksort($vloc);

            //-- Install/Update capital city
            $capital = $repository->findOneBy(['module' => 'none']);
            $capital = $repository->hydrateEntity([
                'module' => 'none',
                'cityName' => getsetting('villagename', LOCATION_FIELDS)
            ], $capital);

            \Doctrine::persist($capital);

            //-- Install/Update cities
            $query = $repository->getQueryBuilder();
            $result = $query->select('u')
                ->from('LotgdCore:ModuleSettings', 'u')
                ->where('u.value IN (:loc) AND u.setting = :set')

                ->setParameter('loc', \array_keys($vloc))
                ->setParameter('set', 'villagename')

                ->getQuery()
                ->getResult()
            ;

            $message = 'flash.message.update.not.found';
            $messageType = 'addWarningMessage';
            $messageParams = [];

            foreach ($result as $value)
            {
                $entity = $repository->findOneBy(['module' => $value->getModulename()]);

                if ($entity)
                {
                    $messageType = 'addInfoMessage';
                    $message = 'flash.message.update.found';
                    $messageParams['location'][] = $value->getValue();
                }

                $entity = $repository->hydrateEntity([
                    'module' => $value->getModulename(),
                    'cityname' => $value->getValue()
                ], $entity);

                \Doctrine::persist($entity);
            }

            if (is_array($messageParams['location'] ?? false))
            {
                $messageParams['count'] = count($messageParams['location']);
                $messageParams['location'] = implode(', ', $messageParams['location']);
            }

            \LotgdFlashMessages::{$messageType}(\LotgdTranslator::t($message, $messageParams, $textDomain));

            \Doctrine::flush();

            return redirect('runmodule.php?module=cityprefs&op=su');
        break;
        case 'editmodule':
        case 'editmodulesave':
            \LotgdNavigation::addHeader('navigation.category.operations');
            \LotgdNavigation::addNav('navigation.nav.city.edit', "runmodule.php?module=cityprefs&op=editcity&cityid={$cityId}");
            \LotgdNavigation::addNav('navigation.nav.city.delete', "runmodule.php?module=cityprefs&op=delcity&cityid={$cityId}", [
                'attributes' => [
                    'onclick' => 'Lotgd.confirm(this, event)',
                    'data-options' => json_encode([
                        'text' => \LotgdTranslator::t('section.delete.confirm', [], $textDomain)
                    ])
                ]
            ]);

            if ($mdule)
            {
                $form = module_objpref_edit('drinks', $mdule, $cityId);

                $params['isLaminas'] = $form instanceof Laminas\Form\Form;
                $params['module'] = $mdule;
                $params['cityId'] = $cityId;

                if ($params['isLaminas'])
                {
                    $form->setAttribute('action', "runmodule.php?module=cityprefs&op=editmodulesave&cityid={$cityId}&mdule={$mdule}&isLaminas=true");
                    $params['formTypeTab'] = $form->getOption('form_type_tab');
                }

                if (\LotgdHttp::isPost())
                {
                    $post = \LotgdHttp::getPostAll();

                    if ($params['isLaminas'])
                    {
                        $form->setData($post);

                        if ($form->isValid())
                        {
                            $data = $form->getData();

                            process_post_save_data($data, $cityId, $mdule);

                            \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.module.saved', [], $textDomain));
                        }
                    }
                    else
                    {
                        reset($post);

                        process_post_save_data($post, $cityId, $mdule);

                        \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.module.saved', [], $textDomain));
                    }
                }

                $params['form'] = $form;

                rawoutput(\LotgdTheme::renderModuleTemplate('cityprefs/run/module.twig', $params));

                \LotgdNavigation::addNavAllow("runmodule.php?module=cityprefs&op=editmodulesave&cityid={$cityId}&mdule={$mdule}");
            }

            \LotgdNavigation::addHeader('navigation.category.prefs');
            module_editor_navs('prefs-city', "runmodule.php?module=cityprefs&op=editmodule&cityid={$cityId}&mdule=");

            page_footer();
        break;

        case 'editcity':
            $params['tpl'] = 'edit';

            if (\LotgdHttp::isPost())
            {
                $post = \LotgdHttp::getPostAll();

                $entity = $repository->find($cityId);
                $entity = $repository->hydrateEntity($post, $entity);

                \Doctrine::persist($entity);
                \Doctrine::flush();

                \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.edit.updated', [
                    'city' => $entity->getCityName(),
                    'module' => $entity->getModule()
                ], $textDomain));
            }

            \LotgdNavigation::addHeader('common.category.navigation', ['textDomain' => 'navigation-app']);
            \LotgdNavigation::addNav('navigation.nav.back.properties', "runmodule.php?module=cityprefs&op=editmodule&cityid=$cityId");

            $params['city'] = $repository->find($cityId);
        break;

        case 'delcity':
            $entity = $repository->find($cityId);

            if ($entity)
            {
                \Doctrine::remove($entity);
                \Doctrine::flush();

                \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.delete.success', ['name' => $entity->getCityName()], $textDomain));
            }

            return redirect('runmodule.php?module=cityprefs&op=su');
        break;
    }

    //-- Restore text domain for navigation
    \LotgdNavigation::setTextDomain();

    rawoutput(LotgdTheme::renderModuleTemplate('cityprefs/run.twig', $params));

    page_footer();
}

function process_post_save_data($data, $id, $module)
{
    foreach ($data as $key => $val)
    {
        if (is_array($val))
        {
            process_post_save_data($val, $id, $module);

            continue;
        }

        set_module_objpref('city', $id, $key, $val, $module);
    }
}
