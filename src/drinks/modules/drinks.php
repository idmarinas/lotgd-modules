<?php

use Symfony\Component\Finder\Finder;
use Tracy\Debugger;
// translator ready
// addnews ready
// mail ready
require_once 'lib/showform.php';

/**
 * REGISTRARLO DESPUES DE ACTIVAR LA VERSIÓN 4.0.0 EN DRACONIA.
 */

/**
 * Date:    Mar 07, 2004
 * Version: 1.0
 * Author:  JT Traub
 * Email:   jtraub@dragoncat.net
 * Purpose:	Provide basic drinks and drunkeness handling.
 *          Subsumes some of the functionality from the drinks module by
 *          John J. Collins (collinsj@yahoo.com).
 */
function drinks_getmoduleinfo()
{
    return [
        'name'     => 'Exotic Drinks',
        'version'  => '6.0.0',
        'author'   => 'John J. Collins<br>Heavily modified by JT Traub, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Inn',
        'download' => 'core_module',
        'settings' => [
            'Drink Module Settings,title',
            'hardlimit' => 'How many hard drinks can a user buy in a day?,int|3',
            'maxdrunk'  => ["How drunk before %s`0 won't serve you?,range,0,100,1|66", LotgdSetting::getSetting('barkeep', '`)Cedrik')],
        ],
        'prefs' => [
            'Drink Module User Preferences,title',
            'drunkeness' => 'Drunkeness,range,0,100,1|0',
            'harddrinks' => 'How many hard drinks has the user bought today?,int|0',
            'canedit'    => 'Has access to the drinks editor,bool|0',
            'noslur'     => "Don't slur speach when drunk,bool|0",
        ],
        'requires' => [
            'lotgd' => '>=6.0.0|Need a version equal or greater than 6.0.0 IDMarinas Edition',
        ],
    ];
}

function drinks_install()
{
    //-- Only insert/update data if alter table structure
    if (Doctrine::createSchema(['LotgdLocal:ModuleDrinks'], true))
    {
        $finder = new Finder();
        $files = $files = $finder->files()->in('modules/drinks/data');

        if (\count($files) > 0)
        {
            try
            {
                $repository = Doctrine::getRepository('LotgdLocal:ModuleDrinks');

                foreach ($files as $file)
                {
                    $data = \json_decode(\file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

                    foreach ($data['rows'] as $row)
                    {
                        $entity = $repository->find($row['id']);
                        $entity = $repository->hydrateEntity($row, $entity);

                        Doctrine::persist($entity);
                    }

                    Doctrine::flush();
                }
            }
            catch (Throwable $th)
            {
                Debugger::log($th);
            }
        }
    }

    // Install the hooks.
    module_addhook('ale');
    module_addhook('newday');
    module_addhook('superuser');
    module_addhook('header-graveyard');
    module_addhook('postcomment');
    module_addhook('soberup');
    module_addhook('dragonkill');

    return true;
}

function drinks_uninstall()
{
    LotgdResponse::pageDebug('Dropping table drinks');
    Doctrine::dropSchema(['LotgdLocal:ModuleDrinks']);

    LotgdResponse::pageDebug('Dropping objprefs related to drinks');
    $objRepository = Doctrine::getRepository('LotgdCore:ModuleObjprefs');
    //-- Updated location
    $query = $objRepository->getQueryBuilder();
    $query->delete('LotgdCore:ModuleObjprefs', 'u')
        ->where('u.objtype = :old')

        ->setParameter('old', 'drinks')

        ->getQuery()
        ->execute()
    ;

    return true;
}

function drinks_dohook($hookname, $args)
{
    global $session;

    switch ($hookname)
    {
        case 'dragonkill':
            set_module_pref('drunkeness', 0);
        break;
        case 'ale':
            require_once 'lib/partner.php';

            $textDomain = modulehook('drinks-text', ['textDomain' => 'drinks_module']);
            $textDomain = $textDomain['textDomain'];

            $hardDrink = (get_module_pref('harddrinks') >= get_module_setting('hardlimit'));

            $where = ['active' => 1];

            if ($hardDrink)
            {
                $where['harddrink'] = 0;
            }

            $drinksRepository = Doctrine::getRepository('LotgdLocal:ModuleDrinks');
            $result           = $drinksRepository->findBy($where, ['costperlevel' => 'ASC']);
            $result           = $drinksRepository->extractEntity($result);

            //-- Check all drinks in one hook
            $result = modulehook('drinks-check', $result);

            foreach ($result as $row)
            {
                if ($row['allowdrink'] ?? false)
                {
                    $drinkcost = $row['costperlevel'] * $session['user']['level'];
                    // No hotkeys on drinks.  Too easy for them to interfere
                    // with and modify stock navs randomly.
                    LotgdNavigation::addNav('navigation.nav.ale', "runmodule.php?module=drinks&act=buy&id={$row['id']}", [
                        'textDomain' => 'drinks_module',
                        'params'     => ['name' => $row['name'], 'cost' => $drinkcost],
                    ]);
                }
            }

            $drunk = (int) get_module_pref('drunkeness');
            $drunk = (int) \min(10, \round($drunk / 10 - .5, 0));
            $drunk = $drunk >= 0 ? \abs($drunk) : -1;

            $args['includeTemplatesPost']['@module/drinks/dohook/ale.twig'] = [
                'textDomain' => $textDomain,
                'barkeep'    => LotgdSetting::getSetting('barkeep', '`tCedrik`0'),
                'innName'    => LotgdSetting::getSetting('innname', LOCATION_INN),
                'userSex'    => $session['user']['sex'],
                'drunk'      => $drunk,
                'hardDrink'  => $hardDrink,
                'partner'    => LotgdTool::getPartner(),
            ];
        break;
        case 'newday':
            set_module_pref('harddrinks', 0);
            $drunk = (int) get_module_pref('drunkeness');

            if ($drunk > 66)
            {
                $args['turnstoday'] .= ', Hangover: -1';

                if (is_module_active('staminasystem'))
                {
                    require_once 'modules/staminasystem/lib/lib.php';

                    removestamina(25000);
                }
                else
                {
                    --$session['user']['turns'];

                    // Sanity check
                    $session['user']['turns'] = \max(0, $session['user']['turns']);
                }

                $args['includeTemplatesPost']['@module/drinks/dohook/newday.twig'] = [
                    'colorCode'     => $ccode,
                    'spec'          => $spec,
                    'bonus'         => $bonus,
                    'staminaSystem' => is_module_active('staminasystem'),
                ];
            }

            set_module_pref('drunkeness', 0);
        break;
        case 'header-graveyard':
            set_module_pref('drunkeness', 0);
        break;
        case 'soberup':
            $soberval = $args['soberval'];
            $sobermsg = $args['sobermsg'];
            $drunk    = get_module_pref('drunkeness');

            if ($drunk > 0)
            {
                $drunk = \round($drunk * $soberval, 0);
                set_module_pref('drunkeness', $drunk);

                if ($sobermsg)
                {
                    LotgdFlashMessages::addInfoMessage($sobermsg);
                }
            }
        break;
        case 'postcomment':
            if (($session['user']['superuser'] & SU_IS_GAMEMASTER) !== 0)
            {
                break;
            }

            require_once 'modules/drinks/drunkenize.php';

            $drunk = get_module_pref('drunkeness');

            if ( ! ($args['data']['command'] ?? false) || '' == $args['data']['command'])
            {
                $args['data']['comment'] = drinks_drunkenize($args['data']['comment'], $drunk);
            }
        break;
        case 'superuser':
            if (($session['user']['superuser'] & SU_EDIT_USERS) || get_module_pref('canedit'))
            {
                LotgdNavigation::addHeader('superuser.category.module', ['textDomain' => 'navigation_app']);
                // Stick the admin=true on so that when we call runmodule it'll
                // work to let us edit drinks even when the module is deactivated.
                LotgdNavigation::addNav('navigation.nav.editor', 'runmodule.php?module=drinks&act=editor&admin=true', ['textDomain' => 'drinks_module']);
            }
        break;
    }//end select

    return $args;
}

function drinks_run()
{
    global $session;

    $act = (string) LotgdRequest::getQuery('act');

    if ( ! \file_exists("modules/drinks/run/{$act}.php"))
    {
        LotgdFlashMessages::addErrorMessage(LotgdTranslator::t('flash.message.', ['file' => $act, 'module' => 'drinks'], 'drinks_module'));

        if ($session['user']['superuser'])
        {
            return redirect('superuser.php');
        }

        return redirect('village.php');
    }

    require_once "modules/drinks/run/{$act}.php";

    //-- Restore text domain for navigation
    LotgdNavigation::setTextDomain();

    LotgdResponse::pageEnd();
}
