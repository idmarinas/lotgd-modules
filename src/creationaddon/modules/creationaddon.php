<?php

/* Name: Creation Addon                                                    */
/* ver 4.0                                                                 */
/* Billie Kennedy => dannic06@gmail.com                                    */

/* ToDo:                                                                   */
/*   Graphic verification for bot killing.                                 */

require_once 'lib/showform.php';

function creationaddon_getmoduleinfo()
{
    return [
        'name'           => 'Creation Addon',
        'version'        => '5.1.1',
        'author'         => 'Billie Kennedy, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category'       => 'Administrative',
        'download'       => '//orpgs.com/modules.php?name=Downloads&d_op=viewdownload&cid=6',
        'vertxtloc'      => '//www.orpgs.com/downloads/',
        'allowanonymous' => true,
        'settings'       => [
            'Create Addon,title',
            'creationmsg'     => 'This is the message to display to new users.,textarea|In order to play The Legend of the Green Dragon you must have read and accepted the following conditions:',
            'requireage'      => 'Do you require the player to be a minimum age?,bool|1',
            'age'             => 'What age to players need to be to play?,int|13',
            'requireterms'    => 'Do you require the player to read the terms?,bool|1',
            'terms'           => 'These are your Terms.,textarea|Some Message',
            'requireprivacy'  => 'Show Privacy Policy?,bool|1',
            'privacy'         => 'This is your Privacy Statement.,textarea|Some Message',
            'askbday'         => 'Ask the player to input thier Birth Day?,bool|0',
            'requirebday'     => 'Do you require the birthday?,bool|0',
            'requireyear'     => 'Do you require the year?,bool|0',
            'chkbday'         => 'Do an Age check?,bool|0',
            'showfooter'      => 'Show your terms/agreements and privacy statment in every footer?,bool|0',
            'filter_titles'   => 'Filter names for titles?,bool|1',
            'filter_badnames' => 'Filter names for badnames?,bool|1',
        ],
        'prefs' => [
            'Creation preferences,title',
            'ageverified'     => 'Players age has been verified?,bool|0',
            'termsverified'   => 'Player has read the terms.,bool|0',
            'privacyverified' => 'Player was shown the Privacy Statment.,bool|0',
            'month'           => 'Birth Month,int|0',
            'day'             => 'Birth Day,int|0',
            'year'            => 'Birth Year,int|0',
        ],
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function creationaddon_install()
{
    \Doctrine::createSchema(['LotgdLocal:ModCreationAddon'], true);

    module_addhook('create-form');
    module_addhook('check-create');
    module_addhook('process-create');
    module_addhook('everyfooter');
    module_addhook('superuser');

    return true;
}

function creationaddon_uninstall()
{
    \Doctrine::dropSchema(['LotgdLocal:ModCreationAddon']);

    return true;
}

function creationaddon_dohook($hookname, $args)
{
    global $session;

    $age     = \LotgdRequest::getPost('age');
    $month   = \LotgdRequest::getPost('month');
    $day     = \LotgdRequest::getPost('day');
    $year    = \LotgdRequest::getPost('year');
    $terms   = \LotgdRequest::getPost('terms');
    $privacy = \LotgdRequest::getPost('privacy');

    switch ($hookname)
    {
        case 'check-create':
            $blockaccount = $args['blockaccount'] ?? false;

            if ( ! isset($args['msg']))
            {
                $args['msg'] = '';
            }
            // We are going to check the bad name list.
            if (get_module_setting('filter_badnames'))
            {
                $name       = (string) \LotgdRequest::getPost('name');
                $repository = \Doctrine::getRepository(\Lotgd\Local\Entity\ModCreationAddon::class);
                $result     = $repository->findAll();

                foreach ($result as $row)
                {
                    $shortname = sanitize_name(getsetting('spaceinname', 0), $name);
                    $pattern   = '/'.$row->getBadName().'/i';

                    if (\preg_match($pattern, $shortname))
                    {
                        $blockaccount = true;
                        \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('check.filterName', [], 'module_creationaddon'));

                        break;
                    }
                }
            }

            // Lets see if they meet the age requirements.
            if (get_module_setting('requireage') && ! $age)
            {
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('check.age', ['age' => (int) get_module_setting('age')], 'module_creationaddon'));

                $blockaccount = true;
            }

            // Did they check the box for terms?
            if (get_module_setting('requireterms') && ! $terms)
            {
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('check.terms', [], 'module_creationaddon'));

                $blockaccount = true;
            }

            // Did they check the box for the Privacy Policy?
            if (get_module_setting('requireprivacy') && ! $privacy)
            {
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('check.privacy', [], 'module_creationaddon'));

                $blockaccount = true;
            }

            if (get_module_setting('chkbday'))
            {
                // Lets do a small check to see if they are actually over the age according to their birthday.
                $thisday   = \date('j');
                $thismonth = \date('n');
                $thisyear  = \date('Y');

                // ok.. lets check to see what month they were born.  if it was after this month then subtract a year.
                if ($thismonth - $month < 0)
                {
                    --$thisyear;
                }

                // they were born the same month as this month.  Lets check the day to see if they have had it yet.
                if ($thisday < $day && 0 == $thismonth - $month)
                {
                    --$thisyear;
                }

                // Lets compare the math in the years.
                if (get_module_setting('requireage') && ($thisyear - $year) < (int) get_module_setting('age'))
                {
                    \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('check.chkbday', [], 'module_creationaddon'));

                    $blockaccount = true;
                }
            }

            $args['blockaccount'] = $blockaccount;

        break;

        case 'create-form':
            $args['templates']['@module/creationaddon/dohook/create-form.twig'] = [
                'creationMessage' => get_module_setting('creationmsg'),
                'requireage'      => (bool) get_module_setting('requireage'),
                'age'             => (int) get_module_setting('age'),
                'requireterms'    => (bool) get_module_setting('requireterms'),
                'requireprivacy'  => (bool) get_module_setting('requireprivacy'),
                'requireyear'     => (bool) get_module_setting('requireyear'),
            ];
        break;

        case 'everyfooter':
            if (get_module_setting('requireprivacy') && get_module_setting('showfooter'))
            {
                $privacy       = \LotgdTranslator::t('privacy', [], 'module_creationaddon');
                $privacyfooter = "<br><a href='runmodule.php?module=creationaddon&op=privacy' target='_blank' onClick=\"Lotgd.embed(this)\" 'class='motd'>{$privacy}</a>";
                \LotgdNavigation::addNavAllow('runmodule.php?module=creationaddon&op=privacy');

                $args['source'] = $args['source'] ?? [];
                $args['source'] = \is_array($args['source']) ? $args['source'] : [$args['source']];

                \array_push($args['source'], $privacyfooter);
            }

            if (get_module_setting('requireterms') && get_module_setting('showfooter'))
            {
                $terms       = \LotgdTranslator::t('terms', [], 'module_creationaddon');
                $termsfooter = "<br><a href='runmodule.php?module=creationaddon&op=terms' target='_blank' onClick=\"Lotgd.embed(this)\" 'class='motd'>{$terms}</a>";
                \LotgdNavigation::addNavAllow('runmodule.php?module=creationaddon&op=terms');

                $args['source'] = $args['source'] ?? [];
                $args['source'] = \is_array($args['source']) ? $args['source'] : [$args['source']];

                \array_push($args['source'], $termsfooter);
            }

        break;

        case 'process-create':
            $id = $args['acctid'];

            if (get_module_setting('requireterms'))
            {
                set_module_pref('termsverified', 1, 'creationaddon', $id);
            }

            if (get_module_setting('requireprivacy'))
            {
                set_module_pref('privacyverified', 1, 'creationaddon', $id);
            }

            if (get_module_setting('requireage'))
            {
                set_module_pref('ageverified', 1, 'creationaddon', $id);
            }

            if (get_module_setting('askbday'))
            {
                set_module_pref('month', $month, 'creationaddon', $id);
                set_module_pref('day', $day, 'creationaddon', $id);

                if (get_module_setting('requireyear'))
                {
                    set_module_pref('year', $year, 'creationaddon', $id);
                }
            }

        break;

        case 'superuser':
            // lets do something here
            if ($session['user']['superuser'] & SU_EDIT_USERS)
            {
                \LotgdNavigation::addHeader('superuser.category.module', ['textDomain' => 'navigation_app']);
                // Stick the admin=true on so that when we call runmodule it'll
                // work to let us edit bad names even when the module is deactivated.
                \LotgdNavigation::addNav('navigation.nav.editor', 'runmodule.php?module=creationaddon&op=list&admin=true', ['textDomain' => 'module_creationaddon']);
            }
        break;
        default: break;
    }

    return $args;
}

function creationaddon_run()
{
    global $session;

    $op = (string) \LotgdRequest::getQuery('op');

    switch ($op)
    {
        case 'list':
            return creationaddon_list();
        break;

        case 'delete':
            return creationaddon_delete();
        break;

        case 'add':
            return creationaddon_add();
        break;
    }

    \LotgdResponse::pageEnd();
}

function creationaddon_list()
{
    global $session;

    \LotgdResponse::pageStart('superuser.editor', [], 'module_creationaddon');

    $page = (int) \LotgdRequest::getQuery('page', 1);
    \LotgdNavigation::superuserGrottoNav();
    creationaddon_menu();

    $repository = \Doctrine::getRepository(\Lotgd\Local\Entity\ModCreationAddon::class);
    $qb         = $repository->createQueryBuilder('u');
    $qb->orderBy('u.badName');

    $paginator = $repository->getPaginator($qb, $page, 50);

    \LotgdNavigation::pagination($paginator, 'runmodule.php?module=creationaddon&op=list');

    $params = [
        'paginator' => $paginator,
    ];
    \LotgdResponse::pageAddContent(LotgdTheme::render('@module/creationaddon/run/list.twig', $params));

    \LotgdResponse::pageEnd();
}

function creationaddon_delete()
{
    global $session;

    $bad_id = (int) \LotgdRequest::getQuery('bad_id', 0);

    \LotgdResponse::pageStart('superuser.editor', [], 'module_creationaddon');

    \LotgdNavigation::superuserGrottoNav();
    creationaddon_menu();

    $repository = \Doctrine::getRepository(\Lotgd\Local\Entity\ModCreationAddon::class);

    $badname = $repository->findOneBy(['id' => $bad_id]);

    if ($badname)
    {
        \LotgdFlashMessages::addSuccessMessage(\LotgdTranslator::t('superuser.delete.success', ['name' => $badname->getBadName()], 'module_creationaddon'));

        \Doctrine::remove($badname);
        \Doctrine::flush();

        return redirect('runmodule.php?module=creationaddon&op=list');
    }

    \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('superuser.delete.fail', [], 'module_creationaddon'));

    return redirect('runmodule.php?module=creationaddon&op=list');
}

function creationaddon_add()
{
    global $session;

    \LotgdResponse::pageStart('superuser.editor', [], 'module_creationaddon');

    \LotgdNavigation::superuserGrottoNav();
    creationaddon_menu();

    $banname = (string) \LotgdRequest::getPost('bname', '');

    if ($banname)
    {
        try
        {
            $repository = \Doctrine::getRepository(\Lotgd\Local\Entity\ModCreationAddon::class);
            $result     = $repository->findOneBy(['badName' => $banname]);

            if ($result)
            {
                \LotgdFlashMessages::addWarningMessage(\LotgdTranslator::t('superuser.add.duplicate', ['name' => $banname], 'module_creationaddon'));

                return redirect('runmodule.php?module=creationaddon&op=add');
            }

            $default = new \Lotgd\Local\Entity\ModCreationAddon();
            $default->setBadName($banname);

            \Doctrine::persist($default);
            \Doctrine::flush();

            \LotgdFlashMessages::addSuccessMessage(\LotgdTranslator::t('superuser.add.success', ['name' => $banname], 'module_creationaddon'));
        }
        catch (\Throwable $th)
        {
            \Tracy\Debugger::log($th);

            \LotgdFlashMessages::addErrorMessage(\LotgdTranslator::t('superuser.add.error', ['name' => $banname], 'module_creationaddon'));
        }

        return redirect('runmodule.php?module=creationaddon&op=add');
    }

    $badnamesarray = [
        'Bad Name,title',
        'bname' => 'Bad Name to Add',
    ];
    $params['form'] = lotgd_showform($badnamesarray, [], true, false, false);

    \LotgdResponse::pageAddContent(LotgdTheme::render('@module/creationaddon/run/add.twig', $params));

    \LotgdResponse::pageEnd();
}

/**
 * Navigation menu.
 */
function creationaddon_menu()
{
    \LotgdNavigation::addHeader('Bad Names Editor');
    \LotgdNavigation::addNav('List Names', 'runmodule.php?module=creationaddon&op=list&admin=true');
    \LotgdNavigation::addNav('Add a Name', 'runmodule.php?module=creationaddon&op=add&admin=true');
}
