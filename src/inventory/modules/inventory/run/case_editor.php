<?php

require_once 'lib/showform.php';

$op2       = (string) \LotgdRequest::getQuery('op2');
$id        = (int) \LotgdRequest::getQuery('id');
$isLaminas = (string) \LotgdRequest::getQuery('isLaminas');

$repository = \Doctrine::getRepository('LotgdLocal:ModInventoryItem');
$buffRepo   = \Doctrine::getRepository('LotgdLocal:ModInventoryBuff');

$params = [
    'textDomain' => 'module_inventory',
    'itemId'     => $id,
];

\LotgdResponse::pageStart('title.editor', [], $params['textDomain']);

\LotgdNavigation::superuserGrottoNav();

\LotgdNavigation::setTextDomain($params['textDomain']);

\LotgdNavigation::addHeader('navigation.category.options.items');
\LotgdNavigation::addNav('navigation.nav.item.new', 'runmodule.php?module=inventory&op=editor&op2=newitem');
\LotgdNavigation::addNav('navigation.nav.item.show', 'runmodule.php?module=inventory&op=editor&op2=showitems');

\LotgdNavigation::addHeader('navigation.category.options.buffs');
\LotgdNavigation::addNav('New Buff', 'runmodule.php?module=inventory&op=editor&op2=newbuff');
\LotgdNavigation::addNav('Show all buffs', 'runmodule.php?module=inventory&op=editor&op2=showbuffs');

\LotgdNavigation::addHeader('navigation.category.options.other');

switch ($op2)
{
    case 'newitem':
        $params['tpl'] = 'edititem';

        $subop  = (string) \LotgdRequest::getQuery('subop');
        $module = (string) \LotgdRequest::getQuery('submodule');
        \LotgdNavigation::addNav('navigation.nav.item.properties', "runmodule.php?module=inventory&op=editor&op2=newitem&id={$id}");
        module_editor_navs('prefs-items', "runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id={$id}&submodule=");

        if ('module' == $subop)
        {
            $form = module_objpref_edit('items', $module, $id);

            $params['isLaminas'] = $form instanceof Laminas\Form\Form;
            $params['module']    = $module;
            $params['id']        = $id;

            if ($params['isLaminas'])
            {
                $form->setAttribute('action', "runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id={$id}&submodule={$module}&isLaminas=true");
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

                        process_post_save_data_inventory($data, $id, $module);

                        \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.actions.save.success', [], $textDomain));
                    }
                }
                else
                {
                    \reset($post);

                    process_post_save_data_inventory($post, $id, $module);

                    \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.actions.save.success', [], $textDomain));
                }
            }

            $params['form'] = $form;

            \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/inventory/run/editor/module.twig', $params));

            \LotgdNavigation::addNavAllow("runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id={$id}&submodule={$module}");
        }
        else
        {
            $lotgdFormFactory = \LotgdLocator::get('Lotgd\Core\SymfonyForm');
            $itemEntity = $repository->find($id) ?: new \Lotgd\Local\Entity\ModInventoryItem();
            \Doctrine::detach($itemEntity);

            $form = $lotgdFormFactory->create(\Lotgd\Local\EntityForm\ModInventoryItemType::class, $itemEntity, [
                'action' => "runmodule.php?module=inventory&op=editor&op2=newitem&id={$id}",
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ]);

            $form->handleRequest();

            if ($form->isSubmitted() && $form->isValid())
            {
                $entity = $form->getData();
                $method = $entity->getId() ? 'merge' : 'persist';

                \Doctrine::{$method}($entity);
                \Doctrine::flush();

                $id = $entity->getId();

                \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.save.saved', [], $textDomain));

                //-- Redo form for change $id and set new data (generated IDs)
                $form = $lotgdFormFactory->create(\Lotgd\Local\EntityForm\ModInventoryItemType::class, $entity, [
                    'action' => "runmodule.php?module=inventory&op=editor&op2=newitem&id={$id}",
                    'attr' => [
                        'autocomplete' => 'off'
                    ]
                ]);
            }

            \LotgdNavigation::addNavAllow("runmodule.php?module=inventory&op=editor&op2=newitem&id={$id}");

            $params['form'] = $form->createView();
            $params['itemId'] = $id;
        }
    break;
    case 'delitem':
        $params['tpl'] = 'delitem';

        $item           = $repository->find($id);
        $params['item'] = clone $item;

        $query               = $repository->getCreateQuery();
        $params['inventory'] = $query->delete('LotgdLocal:ModInventory', 'u')
            ->where('u.item = :id')

            ->setParameter('id', $id)

            ->getQuery()
            ->execute()
        ;

        \LotgdKernel::get('cache.app')->delete('item-activation-fightnav-specialties');
        \LotgdKernel::get('cache.app')->delete('item-activation-forest');
        \LotgdKernel::get('cache.app')->delete('item-activation-train');
        \LotgdKernel::get('cache.app')->delete('item-activation-shades');
        \LotgdKernel::get('cache.app')->delete('item-activation-village');

        modulehook('inventory-delete-item', ['id' => $id]);

        \Doctrine::remove($item);
        \Doctrine::flush();
    break;
    case 'newbuff':
        $params['tpl'] = 'editbuff';

        if (\LotgdRequest::isPost())
        {
            $post = \LotgdRequest::getPostAll();
            \reset($post);

            $message = 'flash.message.save.saved';

            $buff = $buffRepo->find($id);
            $buff = $buffRepo->hydrateEntity($post, $buff);

            \Doctrine::persist($buff);
            \Doctrine::flush();

            $id = $buff->getId();

            if ($message)
            {
                \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.save.buff', [], $params['textDomain']));
            }

            unset($post);
        }

        if ($id)
        {
            $buff = $buffRepo->find($id);

            if ($buff)
            {
                $buff = $buffRepo->extractEntity($buff);
            }
        }

        $buff = \is_array($buff) ? $buff : [];

        $format = [
            'General Settings,title',
            'id'   => 'Buff ID,viewonly',
            'key'  => 'Buff name (shown in editor),string,250',
            'name' => 'Buff name (shown in charstats),string,250',
            'The charstats name will automatically use the color of the skill that uses it,note',
            'rounds' => 'Rounds,int',
            'Combat Modifiers,title',
            'dmgMod'       => 'Damage Modifier (Goodguy)',
            'atkMod'       => 'Attack Modifier (Goodguy)',
            'defMod'       => 'Defense Modifier (Goodguy)',
            'badGuyDmgMod' => 'Damage Modifier (Badguy)',
            'badGuyAtkMod' => 'Attack Modifier (Badguy)',
            'badGuyDefMod' => 'Defense Modifier (Badguy)',
            'Misc Combat Modifiers,title',
            'lifeTap'      => 'Lifetap,float',
            'damageShield' => 'Damage Shield,float',
            'regen'        => 'Regeneration,float',
            'Minion Count Settings,title',
            'minionCount'      => 'Minion count,int',
            'minBadGuyDamage'  => 'Min Badguy Damage,int',
            'maxBadGuyDamage'  => 'Max Badguy Damage,int',
            'minGoodGuyDamage' => 'Max Goodguy Damage,int',
            'maxGoodGuyDamage' => 'Max Goodguy Damage,int',
            'Message Settings,title',
            'You can use %c in any message and it will be replaced with the color code of the skill that activates the buff,note',
            'startMsg'       => 'Start Message',
            'roundMsg'       => 'Round Message',
            'wearOff'        => 'Wear Off Message',
            'effectMsg'      => 'Effect Message',
            'effectFailMsg'  => 'Effect Fail Message',
            'effectNoDmgMsg' => 'Effect No Damage Message',
            'Misc Settings,title',
            'allowInPvp'       => 'Allow in PvP?,bool',
            'allowInTrain'     => 'Allow in Training?,bool',
            'surviveNewDay'    => 'Survive New Day?,bool',
            'invulnerable'     => 'Invulnerable?,bool',
            'expireAfterFight' => 'Expires after fight?,bool',
        ];

        $params['buffId'] = $id;
        $params['form']   = lotgd_showform($format, $buff, true, false, false);
    break;
    case 'delbuff':
        $params['tpl'] = 'delbuff';

        $buff           = $buffRepo->find($id);
        $params['buff'] = clone $buff;

        \Doctrine::remove($buff);
        \Doctrine::flush();
    break;
    case 'showbuffs':
        $params['tpl'] = 'showbuffs';

        $params['results'] = $buffRepo->findAll();
    break;
    case 'takeitem':
        $id = (int) \LotgdRequest::getQuery('id');

        add_item($id);

        \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.item.take', ['itemId' => $id, 'count' => check_qty($id)], $params['textDomain']));
        // no break
    case 'showitems':
    default:
        $params['tpl'] = 'default';

        $params['results'] = $repository->findBy([], ['class' => 'ASC', 'name' => 'ASC']);
    break;
}

\LotgdNavigation::setTextDomain();

\LotgdResponse::pageAddContent(\LotgdTheme::render('@module/inventory/editor.twig', $params));

function process_post_save_data_inventory($data, $id, $module)
{
    foreach ($data as $key => $val)
    {
        if (\is_array($val))
        {
            process_post_save_data_inventory($val, $id, $module);

            continue;
        }

        set_module_objpref('items', $id, $key, $val, $module);
    }
}

\LotgdResponse::pageEnd();
