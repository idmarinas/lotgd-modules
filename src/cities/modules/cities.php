<?php

use Tracy\Debugger;
// translator ready
// addnews ready
// mail ready

function cities_getmoduleinfo()
{
    return [
        'name'                => 'Multiple Cities',
        'version'             => '5.0.0',
        'author'              => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category'            => 'Village',
        'download'            => 'core_module',
        'allowanonymous'      => true,
        'override_forced_nav' => true,
        'settings'            => [
            'Cities Settings,title',
            'allowance'           => 'Daily Travel Allowance,int|3',
            'coward'              => 'Penalise Cowardice for running away?,bool|1',
            'travelspecialchance' => 'Chance for a special during travel,int|7',
            'safechance'          => 'Chance to be waylaid on a safe trip,range,1,100,1|50',
            'dangerchance'        => 'Chance to be waylaid on a dangerous trip,range,1,100,1|66',
        ],
        'prefs' => [
            'Cities User Preferences,title',
            'traveltoday' => 'How many times did they travel today?,int|0',
            'homecity'    => "User's current home city.|",
        ],
        'prefs-mounts' => [
            'Cities Mount Preferences,title',
            'extratravel' => 'How many free travels does this mount give?,int|0',
        ],
        'prefs-drinks' => [
            'Cities Drink Preferences,title',
            'servedcapital' => 'Is this drink served in the capital?,bool|1',
        ],
        'requires' => [
            'lotgd' => '>=6.0.0|Need a version equal or greater than 6.0.0 IDMarinas Edition',
        ],
    ];
}

function cities_install()
{
    module_addhook('village-text-domain');
    module_addhook('village');
    module_addhook('travel');
    module_addhook('count-travels');
    module_addhook('cities-usetravel');
    module_addhook('validatesettings');
    module_addhook('newday');
    module_addhook('charstats');
    module_addhook('mountfeatures');
    module_addhook('faq-toc');
    module_addhook('drinks-check');
    module_addhook('stablelocs');
    module_addhook('camplocs');
    module_addhook('master-autochallenge');

    return true;
}

function cities_uninstall()
{
    // This is semi-unsafe -- If a player is in the process of a page
    // load it could get the location, uninstall the cities and then
    // save their location from their session back into the database
    // I think I have a patch however :)
    $city = LotgdSetting::getSetting('villagename', LOCATION_FIELDS);
    $inn  = LotgdSetting::getSetting('innname', LOCATION_INN);

    try
    {
        $charactersRepository = Doctrine::getRepository('LotgdCore:Avatar');

        //-- Updated location
        $query = $charactersRepository->getQueryBuilder();
        $query->update('LotgdCore:Avatar', 'u')
            ->set('u.location', ':new')
            ->where('u.location = :old')

            ->setParameter('old', $inn)
            ->setParameter('new', $city)

            ->getQuery()
            ->execute()
        ;

        $session['user']['location'] = $city;
    }
    catch (Throwable $th)
    {
        Debugger::log($th);

        return false;
    }

    return true;
}

function cities_dohook($hookname, $args)
{
    global $session;

    $city                        = LotgdSetting::getSetting('villagename', LOCATION_FIELDS);
    $ccity                       = \urlencode($city);
    $session['user']['location'] = $session['user']['location'] ?? '';
    $home                        = $session['user']['location'] == get_module_pref('homecity');
    $capital                     = $session['user']['location'] == $city;

    switch ($hookname)
    {
        case 'validatesettings':
            if ($args['dangerchance'] < $args['safechance'])
            {
                $args['validation_error'] = 'Danger chance must be equal to or greater than the safe chance.';
            }
        break;
        case 'faq-toc':
            $args[] = [
                'onclick' => 'JaxonLotgd.Ajax.Local.ModCities.faq()',
                'link'    => [
                    'section.faq.toc.cities',
                    [],
                    'cities_module',
                ],
            ];
        break;
        case 'drinks-check':
            if ($session['user']['location'] == $city)
            {
                foreach ($args as $key => $drink)
                {
                    $val                      = get_module_objpref('drinks', $drink['id'], 'servedcapital');
                    $args[$key]['allowdrink'] = $val;
                }
            }
        break;
        case 'count-travels':
            global $playermount;

            $args['available'] = $args['available'] ?? 0;
            $args['available'] += get_module_setting('allowance');

            if ($playermount && isset($playermount['mountid']) && $playermount['mountid'])
            {
                $id    = $playermount['mountid'];
                $extra = get_module_objpref('mounts', $id, 'extratravel');
                $args['available'] += $extra;
            }

            $args['used'] = $args['used'] ?? 0;
            $args['used'] += get_module_pref('traveltoday');
        break;
        case 'cities-usetravel':
            global $session;

            $info = modulehook('count-travels', []);

            if ($info['used'] < $info['available'])
            {
                set_module_pref('traveltoday', get_module_pref('traveltoday') + 1);

                if (isset($args['traveltext']))
                {
                    LotgdFlashMessages::addErrorMessage([
                        'message' => $args['traveltext'],
                        'close'   => false,
                    ]);
                }
                $args['success'] = true;
                $args['type']    = 'travel';
            }
            elseif ($session['user']['turns'] > 0)
            {
                --$session['user']['turns'];

                if (isset($args['foresttext']))
                {
                    LotgdFlashMessages::addErrorMessage([
                        'message' => $args['foresttext'],
                        'close'   => false,
                    ]);
                }
                $args['success'] = true;
                $args['type']    = 'forest';
            }
            else
            {
                if (isset($args['nonetext']))
                {
                    LotgdFlashMessages::addErrorMessage([
                        'message' => $args['nonetext'],
                        'close'   => false,
                    ]);
                }
                $args['success'] = false;
                $args['type']    = 'none';
            }

            $args['nocollapse'] = 1;
        break;
        case 'master-autochallenge':
            global $session;

            if (get_module_pref('homecity') != $session['user']['location'])
            {
                $info = modulehook('cities-usetravel',
                    [
                        'foresttext' => LotgdTranslator::t('section.autochallenge.forest', ['location' => $session['user']['location']], 'cities_module'),
                        'traveltext' => LotgdTranslator::t('section.autochallenge.travel', ['location' => $session['user']['location']], 'cities_module'),
                    ]
                    );

                if ($info['success'])
                {
                    if ('travel' == $info['type'])
                    {
                        LotgdLog::debug('Lost a travel because of being truant from master.');
                    }
                    elseif ('forest' == $info['type'])
                    {
                        LotgdLog::debug('Lost a forest fight because of being truant from master.');
                    }
                    else
                    {
                        LotgdLog::debug('Lost something, not sure just what, because of being truant from master.');
                    }
                }
            }
        break;
        case 'mountfeatures':
            $extra                      = get_module_objpref('mounts', $args['id'], 'extratravel');
            $args['features']['Travel'] = $extra;
        break;
        case 'newday':
            if ('true' != $args['resurrection'])
            {
                set_module_pref('traveltoday', 0);
            }

            set_module_pref('paidcost', 0);
        break;
        case 'village-text-domain':
            if ($session['user']['location'] == $city)
            {
                $args['textDomain']           = 'cities_village';
                $args['textDomainNavigation'] = 'cities_navigation';
            }
        break;
        case 'charstats':
            if ($session['user']['alive'])
            {
                addcharstat(LotgdTranslator::t('statistic.category.character.personal', [], 'app_default'));
                addcharstat(LotgdTranslator::t('statistic.stat.home', [], 'cities_module'), get_module_pref('homecity'));

                if ( ! is_module_active('worldmapen'))
                {
                    $args = modulehook('count-travels', ['available' => 0, 'used' => 0]);
                    $free = \max(0, $args['available'] - $args['used']);
                    addcharstat(LotgdTranslator::t('statistic.stat.travels', [], 'cities_module'), $free);
                }
            }
        break;
        case 'village':
            if ($home)
            {
                //-- In home city.
                LotgdNavigation::blockHideLink('inn.php');
                LotgdNavigation::blockHideLink('stables.php');
                LotgdNavigation::blockHideLink('rock.php');
                LotgdNavigation::blockHideLink('mercenarycamp.php');
            }
            elseif ($capital)
            {
                LotgdNavigation::addHeader('headers.fight');
                LotgdNavigation::addNav('navs.healer', 'healer.php?return=village.php');

                //-- In capital city.
                LotgdNavigation::blockHideLink('forest.php');
                LotgdNavigation::blockHideLink('train.php');
                LotgdNavigation::blockHideLink('weapons.php');
                LotgdNavigation::blockHideLink('armor.php');
            }
            else
            {
                //-- In another city.
                LotgdNavigation::blockHideLink('inn.php');
                LotgdNavigation::blockHideLink('stables.php');
                LotgdNavigation::blockHideLink('rock.php');
                LotgdNavigation::blockHideLink('clans.php');
                LotgdNavigation::blockHideLink('hof.php');
                LotgdNavigation::blockHideLink('armor.php');
                LotgdNavigation::blockHideLink('weapons.php');
                LotgdNavigation::blockHideLink('mercenarycamp.php');
            }

            if ( ! is_module_active('worldmapen'))
            {
                LotgdNavigation::addHeader('headers.gate');
                LotgdNavigation::addNav('navs.travel', 'runmodule.php?module=cities&op=travel', ['textDomain' => 'cities_navigation']);
            }

            if (get_module_pref('paidcost') > 0)
            {
                set_module_pref('paidcost', 0);
            }
        break;
        case 'travel':
            $args   = modulehook('count-travels', ['available' => 0, 'used' => 0]);
            $free   = \max(0, $args['available'] - $args['used']);
            $hotkey = 'C';

            //-- Change text domain for navigation
            LotgdNavigation::setTextDomain('cities_navigation');

            LotgdNavigation::addHeader('headers.travelpoints', ['hideEmpty' => false]);
            LotgdNavigation::addHeader('navs.travels', ['hideEmpty' => false, 'params' => ['n' => $free]]);
            LotgdNavigation::addHeader('navs.turns', ['hideEmpty' => false, 'params' => ['n' => $session['user']['turns']]]);

            LotgdNavigation::addHeader('headers.travel.safer');

            if ($session['user']['location'] != $city)
            {
                LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}", [
                    'params' => ['key' => $hotkey, 'city' => $city],
                ]);
            }

            LotgdNavigation::addHeader('headers.travel.dangerous');

            if (($session['user']['superuser'] & SU_EDIT_USERS) !== 0)
            {
                LotgdNavigation::addHeader('headers.superuser');
                LotgdNavigation::addNav('navs.go', "runmodule.php?module=cities&op=travel&city={$ccity}&su=1", [
                    'params' => ['key' => $hotkey, 'city' => $city],
                ]);
            }

            //-- Restore text domain for navigation
            LotgdNavigation::setTextDomain();
        break;
        case 'stablelocs':
        case 'camplocs':
            $args[$city] = ['locs', ['village' => $city], 'cities_module'];
        break;
    }

    return $args;
}

function cities_dangerscale($danger)
{
    global $session;
    $dlevel = ($danger ? get_module_setting('dangerchance') : get_module_setting('safechance'));

    if ($session['user']['dragonkills'] <= 1)
    {
        $dlevel = \round(.50 * $dlevel, 0);
    }
    elseif ($session['user']['dragonkills'] <= 30)
    {
        $scalef = 50                                                     / 29;
        $scale  = (($session['user']['dragonkills'] - 1) * $scalef + 50) / 100;
        $dlevel = \round($scale * $dlevel, 0);
    } // otherwise, dlevel is unscaled.

    return $dlevel;
}

function cities_run()
{
    global $session, $badguy;

    require 'modules/cities/run.php';
}
