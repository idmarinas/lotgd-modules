<?php

require_once 'lib/listfiles.php';
require_once 'lib/showform.php';

$op2 = (string) \LotgdHttp::getQuery('op2');
$id = (int) \LotgdHttp::getQuery('id');

$repository = \Doctrine::getRepository('LotgdLocal:ModInventoryItem');
$buffRepo = \Doctrine::getRepository('LotgdLocal:ModInventoryBuff');

$params = [
    'textDomain' => 'module-inventory',
    'itemId' => $id
];

page_header('title.editor', [], $params['textDomain']);

\LotgdNavigation::superuserGrottoNav();

\LotgdNavigation::setTextDomain($params['textDomain']);

\LotgdNavigation::addHeader('navigation.category.options.items');
\LotgdNavigation::addnav('navigation.nav.item.new', 'runmodule.php?module=inventory&op=editor&op2=newitem');
\LotgdNavigation::addnav('navigation.nav.item.show', 'runmodule.php?module=inventory&op=editor&op2=showitems');

\LotgdNavigation::addHeader('navigation.category.options.buffs');
\LotgdNavigation::addnav('New Buff', 'runmodule.php?module=inventory&op=editor&op2=newbuff');
\LotgdNavigation::addnav('Show all buffs', 'runmodule.php?module=inventory&op=editor&op2=showbuffs');

\LotgdNavigation::addHeader('navigation.category.options.other');

switch ($op2)
{
    case 'newitem':
        $params['tpl'] = 'edititem';

        $subop = (string) \LotgdHttp::getQuery('subop');
        $module = (string) \LotgdHttp::getQuery('submodule');

        if (\LotgdHttp::isPost())
        {
            $post = \LotgdHttp::getPostAll();
            reset($post);

            $message = '';
            $paramsFlashMessage = [];

            if ('module' == $subop)
            {
                $message = 'flash.message.save.module';
                $paramsFlashMessages = ['name' => $module];

                // Save modules settings
                foreach ($post as $key => $val)
                {
                    set_module_objpref('items', $id, $key, $val, $module);
                }
            }
            else
            {
                $message = 'flash.message.save.saved';

                $post['activationHook'] = array_sum(array_keys($post['activationHook']));
                $post['buff'] = $buffRepo->find($post['buff']);

                $item = $repository->find($id);
                $item = $repository->hydrateEntity($post, $item);

                \Doctrine::persist($item);
                \Doctrine::flush();

                $id = $item->getId();
            }

            if ($message)
            {
                \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t($message, $paramsFlashMessage, $params['textDomain']));
            }

            unset($post);
        }

        if ($id)
        {
            $item = $repository->find($id);
            if ($item)
            {
                $item = $repository->extractEntity($item);
                $item['buff'] = $item['buff']->getId();
            }

            \LotgdNavigation::addNav('navigation.nav.item.properties', "runmodule.php?module=inventory&op=editor&op2=newitem&id={$id}");
            module_editor_navs('prefs-items', "runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id={$id}&submodule=");
        }

        $item = is_array($item) ? $item : [];

        if ('module' == $subop)
        {
            rawoutput("<form action='runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id={$id}&submodule={$module}' method='POST'>");
            module_objpref_edit('items', $module, $id);
            rawoutput('</form>');
            \LotgdNavigation::addNavAllow("runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id={$id}&submodule={$module}");
        }
        else
        {
            $result = $buffRepo->findAll();

            $buffsjoin = '0,none';
            foreach ($result as $row)
            {
                $key = str_replace(',', ' ', $row->getKey());
                $name = str_replace(',', ' ', $row->getName());

                $buffsjoin .= ",{$row->getId()},{$name} ({$key})";
            }

            $enum_equip = 'none,' . \LotgdTranslator::t('equipment.none', [], $textDomain)
                . ',head,' . \LotgdTranslator::t('equipment.head', [], $textDomain)
                . ',armor,' . \LotgdTranslator::t('equipment.armor', [], $textDomain)
                . ',mainhand,' . \LotgdTranslator::t('equipment.mainhand', [], $textDomain)
                . ',belt,' . \LotgdTranslator::t('equipment.belt', [], $textDomain)
                . ',offhand,' . \LotgdTranslator::t('equipment.offhand', [], $textDomain)
                . ',righthand,' . \LotgdTranslator::t('equipment.righthand', [], $textDomain)
                . ',trausers,' . \LotgdTranslator::t('equipment.trausers', [], $textDomain)
                . ',lefthand,' . \LotgdTranslator::t('equipment.lefthand', [], $textDomain)
                . ',rightring,' . \LotgdTranslator::t('equipment.rightring', [], $textDomain)
                . ',feet,' . \LotgdTranslator::t('equipment.feet', [], $textDomain)
                . ',leftring,' . \LotgdTranslator::t('equipment.leftring', [], $textDomain)
            ;

            $sort = list_files('items', []);
            sort($sort);
            $scriptenum = implode('', $sort);
            $scriptenum = ',,none'.$scriptenum;

            $sort = list_files('items_requisites', []);
            sort($sort);
            $scriptenumrequisite = implode('', $sort);
            $scriptenumrequisite = ',,none'.$scriptenumrequisite;

            $sort = list_files('items_customvalue', []);
            sort($sort);
            $scriptenumcustomvalue = implode('', $sort);
            $scriptenumcustomvalue = ',,none'.$scriptenumcustomvalue;

            $format = [
                'Basic information,title',
                    'id' => 'Item id,viewhiddenonly',
                    'class' => 'Item category, string|Loot',
                    'name' => 'Item name, string|',
                    'image' => 'Item image (class code for CSS image), string|',
                    'description' => 'Description, textarea,60,5|Just a normal useless item.',
                'Values,title',
                    'gold' => 'Gold value,int|0',
                    'gems' => 'Gem value,int|0',
                    'weight' => 'Weight,int|1',
                    'droppable' => 'Is this item droppable,bool',
                    'level' => 'Minimum level needed,range,1,15,1|1',
                    'dragonkills' => 'Dragonkills needed,int|0',
                    'customValue' => 'Custom detailed information (show in shop for example),textarea',
                    'execCustomValue' => 'Custom exec value for detailed information (this information need process),enumsearch'.$scriptenumcustomvalue,
                    'exectext' => 'Text to display upon activation of the item,string,100',
                    "Use %s to insert the item's name!,note",
                    'noEffectText' => 'Text to display if item has no effect,string,100',
                    'execValue' => 'Exec value file,enumsearch'.$scriptenum,
                    'execrequisites' => 'Exec custom requisites,enumsearch'.$scriptenumrequisite,
                    "Please see the file 'lib/itemeffects.php' for possible values,note",
                    'hide' => 'Hide item from inventory?,bool',
                'Buffs and activation,title',
                    'buff' => "Activate this buff on useage,enum,$buffsjoin",
                    'charges' => 'Amount of charges the item has,int|0',
                    'link' => "Link that's called upon activation,|",
                    'activationHook' => 'Hooks which show the item,bitfield,127,'
                        .HOOK_NEWDAY.',Newday,'
                        .HOOK_FOREST.',Forest,'
                        .HOOK_VILLAGE.',Village,'
                        .HOOK_SHADES.',Shades,'
                        .HOOK_FIGHTNAV.',Fightnav,'
                        .HOOK_TRAIN.',Train,'
                        .HOOK_INVENTORY.',Inventory',
                'Chances,title',
                    'find_rarity' => 'Rarity of object, enum,common,Common,uncommon,Uncommon,rare,Rare,legend,Legend',
                    'findChance' => "Chance to get this item though 'get_random_item()',range,0,100,1|100",
                    'looseChance' => 'Chance that this item gets damaged when dying in battle,range,0,100,1|100',
                    'dkLooseChance' => 'Chance to loose this item after killing the dragon,range,0,100,1|100',
                'Shop Options,title',
                    'sellable' => 'Is this item sellable?,bool',
                    'buyable' => 'Is this item buyable?,bool',
                'Special Settings,title',
                    'uniqueForServer' => 'Is this item unique (server)?,bool',
                    'uniqueForPlayer' => 'Is this item unique for the player?,bool',
                    'equippable' => 'Is this item equippable?,bool',
                    'equipWhere' => "Where can this item be equipped?,enum,$enum_equip",
            ];

            $params['form'] = lotgd_showform($format, $item, true, false, false);
        }
    break;
    case 'delitem':
        $params['tpl'] = 'delitem';

        $item = $repository->find($id);
        $params['item'] = clone $item;

        $query = $repository->getCreateQuery();
        $params['inventory'] = $query->delete('LotgdLocal:ModInventory', 'u')
            ->where('u.item = :id')

            ->setParameter('id', $id)

            ->getQuery()
            ->execute()
        ;

        invalidatedatacache('item-activation-fightnav-specialties');
        invalidatedatacache('item-activation-forest');
        invalidatedatacache('item-activation-train');
        invalidatedatacache('item-activation-shades');
        invalidatedatacache('item-activation-village');

        modulehook('inventory-delete-item', ['id' => $id]);

        \Doctrine::remove($item);
        \Doctrine::flush();
    break;
    case 'newbuff':
        $params['tpl'] = 'editbuff';

        if (\LotgdHttp::isPost())
        {
            $post = \LotgdHttp::getPostAll();
            reset($post);

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

        $buff = is_array($buff) ? $buff : [];

        $format = [
            'General Settings,title',
                'id' => 'Buff ID,viewonly',
                'key' => 'Buff name (shown in editor),string,250',
                'name' => 'Buff name (shown in charstats),string,250',
                'The charstats name will automatically use the color of the skill that uses it,note',
                'rounds' => 'Rounds,string,250',
            'Combat Modifiers,title',
                'dmgMod' => 'Damage Modifier (Goodguy),string',
                'atkMod' => 'Attack Modifier (Goodguy),string',
                'defMod' => 'Defense Modifier (Goodguy),string',
                'badGuyDmgMod' => 'Damage Modifier (Badguy),string',
                'badGuyAtkMod' => 'Attack Modifier (Badguy),string',
                'badGuyDefMod' => 'Defense Modifier (Badguy),string',
            'Misc Combat Modifiers,title',
                'lifeTap' => 'Lifetap,string,250',
                'damageShield' => 'Damage Shield,string,250',
                'regen' => 'Regeneration,string,250',
            'Minion Count Settings,title',
                'minionCount' => 'Minion count,string,250',
                'minBadGuyDamage' => 'Min Badguy Damage,string,250',
                'maxBadGuyDamage' => 'Max Badguy Damage,string,250',
                'minGoodGuyDamage' => 'Max Goodguy Damage,string,250',
                'maxGoodGuyDamage' => 'Max Goodguy Damage,string,250',
            'Message Settings,title',
                'You can use %c in any message and it will be replaced with the color code of the skill that activates the buff,note',
                'startMsg' => 'Start Message,string,250',
                'round;sg' => 'Round Message,string,250',
                'wearFff' => 'Wear Off Message,string,250',
                'effectMsg' => 'Effect Message,string,250',
                'effectFailMsg' => 'Effect Fail Message,string,250',
                'effectNoDmgMsg' => 'Effect No Damage Message,string,250',
            'Misc Settings,title',
                'allowInPvp' => 'Allow in PvP?,bool',
                'allowInTrain' => 'Allow in Training?,bool',
                'surviveNewDay' => 'Survive New Day?,bool',
                'invulnerable' => 'Invulnerable?,bool',
                'expireAfterFight' => 'Expires after fight?,bool',
        ];

        $params['buffId'] = $id;
        $params['form'] = lotgd_showform($format, $buff, true, false, false);
    break;
    case 'delbuff':
        $params['tpl'] = 'delbuff';

        $buff = $buffRepo->find($id);
        $params['buff'] = clone $buff;

        \Doctrine::remove($buff);
        \Doctrine::flush();
    break;
    case 'showbuffs':
        $params['tpl'] = 'showbuffs';

        $params['results'] = $buffRepo->findAll();
    break;
    case 'takeitem':
        $id = (int) \LotgdHttp::getQuery('id');

        add_item($id);

        \LotgdFlashMessages::addInfoMessage(\LotgdTranslator::t('flash.message.item.take', [ 'itemId' => $id, 'count' => check_qty($id) ], $params['textDomain']));
    case 'showitems':
    default:
        $params['tpl'] = 'default';

        $params['results'] = $repository->findBy([], [ 'class' => 'ASC' , 'name' => 'ASC' ]);
    break;
}

\LotgdNavigation::setTextDomain();

rawoutput(\LotgdTheme::renderModuleTemplate('inventory/editor.twig', $params));

page_footer();
