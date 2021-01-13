<?php

if ( ! get_module_pref('canedit'))
{
    check_su_access(SU_EDIT_USERS);
}

$textDomain = 'drinks-module';

\LotgdResponse::pageStart('editor.title', [], $textDomain);

\LotgdNavigation::superuserGrottoNav();

//-- Change text domain for navigation
\LotgdNavigation::setTextDomain('drinks-module');

\LotgdNavigation::addHeader('navigation.category.editor');
\LotgdNavigation::addNav('navigation.nav.add', 'runmodule.php?module=drinks&act=editor&op=add&admin=true');

$op      = (string) \LotgdRequest::getQuery('op');
$drinkid = (int) \LotgdRequest::getQuery('drinkid');
$subop   = (string) \LotgdRequest::getQuery('subop');
$page    = (int) \LotgdRequest::getQuery('page');

$repository = \Doctrine::getRepository('LotgdLocal:ModuleDrinks');

$header = 'editor.subtitle.current';

if ('' != $op)
{
    \LotgdNavigation::addNav('navigation.nav.main', 'runmodule.php?module=drinks&act=editor&admin=true');

    if ('add' == $op)
    {
        $header = 'editor.subtitle.adding';
    }
    elseif ('edit' == $op)
    {
        $header = 'editor.subtitle.editing';
    }
}

$params = [
    'textDomain' => $textDomain,
    'subTitle'   => $header,
];

$drinksForm = [
    'Drink,title',
    'drinkid'            => 'Drink ID,hidden',
    'name'               => 'Drink Name',
    'costperlevel'       => 'Cost per level,int',
    'hpchance'           => 'Chance of modifying HP (see below),range,0,10,1',
    'turnchance'         => 'Chance of modifying turns (see below),range,0,10,1',
    'alwayshp'           => 'Always modify hitpoints,bool',
    'alwaysturn'         => 'Always modify turns,bool',
    'drunkeness'         => 'Drunkeness,range,1,100,1',
    'harddrink'          => 'Is drink hard alchohol?,bool',
    'hpmin'              => 'Min HP to add (see below),range,-20,20,1',
    'hpmax'              => 'Max HP to add (see below),range,-20,20,1',
    'hppercent'          => 'Modify HP by some percent (see below),range,-25,25,5',
    'turnmin'            => 'Min turns to add (see below),range,-5,5,1',
    'turnmax'            => 'Max turns to add (see below),range,-5,5,1',
    'remarks'            => 'Remarks',
    'buffname'           => 'Name of the buff',
    'buffrounds'         => 'Rounds buff lasts,range,1,20,1',
    'buffroundmsg'       => 'Message each round of buff',
    'buffwearoff'        => 'Message when buff wears off',
    'buffatkmod'         => 'Attack modifier of buff',
    'buffdefmod'         => 'Defense modifier of buff',
    'buffdmgmod'         => 'Damage modifier of buff',
    'buffdmgshield'      => 'Damage shield modifier of buff',
    'buffeffectfailmsg'  => 'Effect failure message (see below)',
    'buffeffectnodmgmsg' => 'No damage message (see below)',
    'buffeffectmsg'      => 'Effect message (see below)',
];

//-- Change text domain for navigation
\LotgdNavigation::setTextDomain('drinks-module');

\LotgdNavigation::addNav('navigation.nav.update', 'runmodule.php?module=drinks&act=editor');

if ('del' == $op)
{
    $entity = $repository->find($drinkid);

    $message = 'flash.message.editor.remove.error';

    if ($entity)
    {
        $message = 'flash.message.editor.remove.success';
        \Doctrine::remove($entity);
        \Doctrine::flush();
    }

    \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t($message, [], $textDomain));

    module_delete_objprefs('drinks', $drinkid);

    $op = '';
    \LotgdRequest::setQuery('op', '');
}
elseif ('save' == $op)
{
    $subop = \LotgdRequest::getQuery('subop');
    $post  = \LotgdRequest::getPostAll();

    $message            = '';
    $paramsFlashMessage = [];

    if ('module' == $subop)
    {
        $module = \LotgdRequest::getQuery('editmodule');

        $message            = 'flash.message.editor.save.module';
        $paramsFlashMessage = ['name' => $module];
        // Save module settings
        \reset($post);

        foreach ($post as $key => $val)
        {
            set_module_objpref('drinks', $drinkid, $key, $val, $module);
        }
    }
    else
    {
        $message = 'flash.message.editor.save.saved';

        $drinkEntity = $repository->find($drinkid);
        $drinkEntity = $repository->hydrateEntity($post, $drinkEntity);

        \Doctrine::persist($drinkEntity);
        \Doctrine::flush();

        $drinkid = $drinkEntity->getId();
    }

    if ($message)
    {
        \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t($message, $paramsFlashMessage, $textDomain));
    }

    $op = 'edit';
    \LotgdRequest::setQuery('op', $op);
    unset($message, $drinkEntity, $post);
}
elseif ('activate' == $op)
{
    $drinkEntity = $repository->find($drinkid);

    if ($drinkEntity)
    {
        $drinkEntity->setActive(true);

        \Doctrine::persist($drinkEntity);
        \Doctrine::flush();
    }

    $op = '';
    \LotgdRequest::setQuery('op', '');
}
elseif ('deactivate' == $op)
{
    $drinkEntity = $repository->find($drinkid);

    if ($drinkEntity)
    {
        $drinkEntity->setActive(false);

        \Doctrine::persist($drinkEntity);
        \Doctrine::flush();
    }

    $op = '';
    \LotgdRequest::setQuery('op', '');
}

if ('' == $op)
{
    $params['tpl'] = 'default';

    $drinksRepository = \Doctrine::getRepository('LotgdLocal:ModuleDrinks');

    $query = $drinksRepository->createQueryBuilder('u');
    $query->orderBy('u.id', 'ASC');

    $params['paginator'] = $drinksRepository->getPaginator($query, $page, 50);
}
elseif ('edit' == $op || 'add' == $op)
{
    $params['tpl'] = 'edit';

    \LotgdNavigation::addNav('navigation.nav.properties', "runmodule.php?module=drinks&act=editor&op=edit&drinkid={$drinkid}&admin=true");

    module_editor_navs('prefs-drinks', "runmodule.php?module=drinks&act=editor&drinkid={$drinkid}&op=edit&subop=module&editmodule=");

    if ('module' == $subop)
    {
        $module = \LotgdRequest::getQuery('editmodule');

        $form = module_objpref_edit('drinks', $module, $drinkid);

        $params['isLaminas'] = $form instanceof Laminas\Form\Form;
        $params['module']    = $module;
        $params['drinkid']   = $drinkid;

        if ($params['isLaminas'])
        {
            $form->setAttribute('action', "runmodule.php?module=drinks&act=editor&op=edit&subop=module&editmodule={$module}&drinkid={$drinkid}&admin=true&isLaminas=true");
            $params['formTypeTab'] = $form->getOption('form_type_tab');
        }

        if (\LotgdRequest::isPost())
        {
            $post = \LotgdRequest::getPostAll();

            if ($params['isLaminas'])
            {
                $form->setData($post);

                if ($form->isValid())
                {
                    $data = $form->getData();

                    process_post_save_data_drinks($data, $drinkid, $module);

                    \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.actions.save.success', [], $textDomain));
                }
            }
            else
            {
                \reset($post);

                process_post_save_data_drinks($post, $drinkid, $module);

                \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.actions.save.success', [], $textDomain));
            }
        }

        $params['form'] = $form;

        \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/drinks/run/edit/module.twig', $params));

        \LotgdNavigation::addNavAllow("runmodule.php?module=drinks&act=editor&op=edit&subop=module&editmodule={$module}&drinkid={$drinkid}&admin=true");
    }
    elseif ('' == $subop)
    {
        $drinkEntity = $repository->find($drinkid);
        $drinkArray  = $drinkEntity ? $repository->extractEntity($drinkEntity) : [];

        $params['form']  = lotgd_showform($drinksForm, $drinkArray, false, false, false);
        $params['drink'] = $drinkArray;
    }
}

\LotgdResponse::pageAddContent(\LotgdTheme::render('@module/drinks/run/superuser.twig', $params));

function process_post_save_data_drinks($data, $id, $module)
{
    foreach ($data as $key => $val)
    {
        if (\is_array($val))
        {
            process_post_save_data_drinks($val, $id, $module);

            continue;
        }

        set_module_objpref('drinks', $id, $key, $val, $module);
    }
}
