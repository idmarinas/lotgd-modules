<?php

require_once 'lib/forestoutcomes.php';

$op = \LotgdHttp::getQuery('op');
$city = (string) urldecode(\LotgdHttp::getQuery('city'));
$ccity = urlencode($city);
$continue = \LotgdHttp::getQuery('continue');
$danger = \LotgdHttp::getQuery('d');
$su = \LotgdHttp::getQuery('su');

if ('faq' != $op)
{
    require_once 'lib/forcednavigation.php';
    do_forced_nav(false, false);
}

//-- Change text domain for navigation
\LotgdNavigation::setTextDomain('cities-navigation');

// I really don't like this being out here, but it has to be since
// events can define their own op=.... and we might need to handle them
// otherwise things break.
require_once 'lib/events.php';

if (! isset($session['user']['specialinc']) || '' != $session['user']['specialinc'] || \LotgdHttp::getQuery('eventhandler'))
{
    $in_event = handle_event('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1&", 'Travel');

    if ($in_event)
    {
        \LotgdNavigation::addNav('common.nav.continue', "runmodule.php?module=cities&op=travel&city={$ccity}&d={$danger}&continue=1", [
            'textDomain' => 'navigation-app'
        ]);

        module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");

        page_footer();
    }
}

if ('travel' == $op)
{
    $args = modulehook('count-travels', ['available' => 0, 'used' => 0]);
    $free = max(0, $args['available'] - $args['used']);

    if ('' == $city)
    {
        page_header('title.travel', [], 'cities-module');

        addnav('Forget about it');
        \LotgdNavigation::villageNav();

        modulehook('pre-travel');

        $params['canTravel'] = (! ($session['user']['superuser'] & SU_EDIT_USERS) && ($session['user']['turns'] <= 0) && 0 == $free);

        if ($params['canTravel'])
        {
            \LotgdNavigation::addHeader('category.travel', [ 'textDomain' => 'cities-navigation']);
            modulehook('travel');
            // this line rewritten so as not to clash with the hitch module.
        }

        module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");

        rawoutput(LotgdTheme::renderModuleTemplate('cities/run/travel.twig', $params));

        page_footer();
    }
    else
    {
        if ('1' != $continue && '1' != $su && ! get_module_pref('paidcost'))
        {
            set_module_pref('paidcost', 1);
            $httpcost = \LotgdHttp::getQuery('cost');
            $cost = modulehook('travel-cost', ['from' => $session['user']['location'], 'to' => $city, 'cost' => 0]);
            $cost = max(1, $cost['cost'], $httpcost);
            $reallyfree = $free - $cost;

            if ($reallyfree > 0)
            {
                // Only increment travel used if they are still within
                // their allowance.
                increment_module_pref('traveltoday', $cost);
            //do nothing, they're within their travel allowance.
            }
            elseif ($session['user']['turns'] + $free > 0)
            {
                $over = abs($reallyfree);
                increment_module_pref('traveltoday', $free);
                $session['user']['turns'] -= $over;
            }
            else
            {
                \LotgdFlashMessages::addInfoMessage([
                    'message' => \LotgdTranslator::t('flash.message.not.forest.fights', [], 'cities-module'),
                    'close' => false
                ]);

                debuglog("Travelled with out having any forest fights, how'd they swing that?");
            }
        }

        // Let's give the lower DK people a slightly better chance.
        $dlevel = cities_dangerscale($danger);

        if (e_rand(0, 100) < $dlevel && '1' != $su)
        {
            //they've been waylaid.

            if (0 != module_events('travel', get_module_setting('travelspecialchance'), "runmodule.php?module=cities&city={$ccity}&d=$dangecontinue=1&"))
            {
                page_header('section.title.event', [], 'cities-module');

                if (\LotgdNavigation::checkNavs())
                {
                    page_footer();
                }
                else
                {
                    // Reset the special for good.
                    $session['user']['specialinc'] = '';
                    $session['user']['specialmisc'] = '';
                    $skipvillagedesc = true;
                    $op = '';
                    \LotgdHttp::setQuery('op', '');

                    \LotgdNavigation::addNav('navs.continue', "runmodule.php?module=cities&op=travel&city={$ccity}&d={$danger}&continue=1", [
                        'textDomain' => 'cities-navigation'
                    ]);

                    module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");

                    page_footer();
                }
            }

            $args = [
                'soberval' => 0.9,
                'sobermsg' => \LotgdTranslator::t('section.travel.sobermsg', [], 'cities-module'),
                'schema' => 'module-cities'
            ];
            modulehook('soberup', $args);

            $result = lotgd_search_creature(1, $session['user']['level'], $session['user']['level']);

            if (! count($result))
            {
                // There is nothing in the database to challenge you,
                // let's give you a doppleganger.
                $badguy = lotgd_generate_doppelganger($session['user']['level']);
            }
            else
            {
                $badguy = $result[0];
                $badguy = buffbadguy($badguy);
            }

            calculate_buff_fields();
            $badguy['playerstarthp'] = $session['user']['hitpoints'];
            $badguy['diddamage'] = 0;
            $badguy['type'] = 'travel';
            $session['user']['badguy'] = $badguy;
            $battle = true;
        }
        else
        {
            set_module_pref('paidcost', 0);
            //they arrive with no further scathing.
            $session['user']['location'] = $city;

            return redirect('village.php');
        }
    }
}
elseif ('fight' == $op || 'run' == $op)
{
    if ('run' == $op && e_rand(1, 5) < 3)
    {
        // They managed to get away.
        page_header('title.escape', [], 'cities-module');

        $coward = get_module_setting('coward');

        if ($coward)
        {
            modulehook('cities-usetravel',
                [
                    'foresttext' => \LotgdTranslator::t('section.escape.coward.forest', [], 'cities-module'),
                    'traveltext' => \LotgdTranslator::t('section.escape.coward.travel', [], 'cities-module'),
                ]
            );
        }

        $params = [
            'location' => $session['user']['location']
        ];

        \LotgdNavigation::addNav('navs.enter', [ 'location' => $session['user']['location'] ], 'village.php');

        rawoutput(LotgdTheme::renderModuleTemplate('cities/run/escape.twig', $params));

        page_footer();
    }

    $battle = true;
}
elseif ('faq' == $op)
{
    popup_header('section.faq.title', [], 'cities-module');

    $newbieisland = get_module_setting('villagename', 'newbieisland');

    $params = [
        'travels' => get_module_setting('allowance'),
        'capital' => getsetting('villagename', LOCATION_FIELDS),
        'lodge' => file_exists('public/lodge.php'),
        'newbieIsland' => is_module_active('newbieisland'),
        'newbieIslandName' => $newbieisland,
        'location' => (isset($session['user']['location']) && $session['user']['location'] == $newbieisland)
    ];

    rawoutput(LotgdTheme::renderModuleTemplate('cities/run/faq.twig', $params));

    popup_footer();
}
elseif ('' == $op)
{
    page_header('title.travel', [], 'cities-module');

    \LotgdFlashMessages::addInfoMessage([
        'message' => \LotgdTranslator::t('section.travel.empty', [], 'citites-module'),
        'close' => false
    ]);

    \LotgdNavigation::addNav('navs.journey', "runmodule.php?module=cities&op=travel&city={$ccity}&continue=1&d={$danger}");

    module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");

    page_footer();
}

if ($battle)
{
    page_header('title.battle', [], 'cities-module');

    $battleDefeatWhere = false;

    require_once 'battle.php';

    if ($victory)
    {
        \LotgdNavigation::addNav('navs.journey', "runmodule.php?module=cities&op=travel&city={$ccity}&continue=1&d={$danger}");

        module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");
    }
    elseif ($defeat)
    {
        addnews('travel.deathmessage', [
            'location' => $city,
            'player' => $session['user']['name'],
            'creature' => $badbuy['creaturename']
        ], 'cities-module');
    }
    else
    {
        require_once 'lib/fightnav.php';
        fightnav(true, true, "runmodule.php?module=cities&city={$ccity}&d={$danger}");
    }

    page_footer();
}

//-- Restore text domain for navigation
\LotgdNavigation::setTextDomain();
