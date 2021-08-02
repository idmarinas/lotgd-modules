<?php

require_once 'lib/forestoutcomes.php';

$op       = \LotgdRequest::getQuery('op');
$city     = (string) \urldecode(\LotgdRequest::getQuery('city'));
$ccity    = \urlencode($city);
$continue = \LotgdRequest::getQuery('continue');
$danger   = \LotgdRequest::getQuery('d');
$su       = \LotgdRequest::getQuery('su');

if ('faq' != $op)
{
    require_once 'lib/forcednavigation.php';
    do_forced_nav(false, false);
}

//-- Change text domain for navigation
\LotgdNavigation::setTextDomain('cities_navigation');

// I really don't like this being out here, but it has to be since
// events can define their own op=.... and we might need to handle them
// otherwise things break.
require_once 'lib/events.php';

if ( ! isset($session['user']['specialinc']) || '' != $session['user']['specialinc'] || \LotgdRequest::getQuery('eventhandler'))
{
    $in_event = handle_event('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1&", 'Travel');

    if ($in_event)
    {
        \LotgdNavigation::addNav('common.nav.continue', "runmodule.php?module=cities&op=travel&city={$ccity}&d={$danger}&continue=1", [
            'textDomain' => 'navigation_app',
        ]);

        module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");

        \LotgdResponse::pageEnd();
    }
}

if ('travel' == $op)
{
    $args = modulehook('count-travels', ['available' => 0, 'used' => 0]);
    $free = \max(0, $args['available'] - $args['used']);

    if ('' == $city)
    {
        \LotgdResponse::pageStart('title.travel', [], 'cities_module');

        \LotgdNavigation::addHeader('headers.forget', ['textDomain' => 'cities_navigation']);
        \LotgdNavigation::villageNav();

        modulehook('pre-travel');

        $params['canTravel'] = ! ( ! ($session['user']['superuser'] & SU_EDIT_USERS) && ($session['user']['turns'] <= 0) && 0 == $free);

        if ($params['canTravel'])
        {
            \LotgdNavigation::addHeader('headers.travel', ['textDomain' => 'cities_navigation']);
            modulehook('travel');
            // this line rewritten so as not to clash with the hitch module.
        }

        \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/cities/run/travel.twig', $params));

        module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");

        \LotgdResponse::pageEnd();
    }
    else
    {
        if ('1' != $continue && '1' != $su && ! get_module_pref('paidcost'))
        {
            set_module_pref('paidcost', 1);
            $httpcost   = \LotgdRequest::getQuery('cost');
            $cost       = modulehook('travel-cost', ['from' => $session['user']['location'], 'to' => $city, 'cost' => 0]);
            $cost       = \max(1, $cost['cost'], $httpcost);
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
                $over = \abs($reallyfree);
                increment_module_pref('traveltoday', $free);
                $session['user']['turns'] -= $over;
            }
            else
            {
                \LotgdFlashMessages::addInfoMessage([
                    'message' => \LotgdTranslator::t('flash.message.not.forest.fights', [], 'cities_module'),
                    'close'   => false,
                ]);

                \LotgdLog::debug("Travelled with out having any forest fights, how'd they swing that?");
            }
        }

        // Let's give the lower DK people a slightly better chance.
        $dlevel = cities_dangerscale($danger);

        if (\mt_rand(0, 100) < $dlevel && '1' != $su)
        {
            //they've been waylaid.

            if (0 != module_events('travel', get_module_setting('travelspecialchance'), "runmodule.php?module=cities&city={$ccity}&d={$dangecontinue}=1&"))
            {
                \LotgdResponse::pageStart('section.title.event', [], 'cities_module');

                if (\LotgdNavigation::checkNavs())
                {
                    \LotgdResponse::pageEnd();
                }
                else
                {
                    // Reset the special for good.
                    $session['user']['specialinc']  = '';
                    $session['user']['specialmisc'] = '';
                    $skipvillagedesc                = true;
                    $op                             = '';
                    \LotgdRequest::setQuery('op', '');

                    \LotgdNavigation::addNav('navs.continue', "runmodule.php?module=cities&op=travel&city={$ccity}&d={$danger}&continue=1", [
                        'textDomain' => 'cities_navigation',
                    ]);

                    module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");

                    \LotgdResponse::pageEnd();
                }
            }

            $args = [
                'soberval' => 0.9,
                'sobermsg' => \LotgdTranslator::t('section.travel.sobermsg', [], 'cities_module'),
                'schema'   => 'module-cities',
            ];
            modulehook('soberup', $args);

            $result = LotgdKernel::get('lotgd_core.tool.creature_functions')->lotgdSearchCreature(1, $session['user']['level'], $session['user']['level']);

            if ( ! \count($result))
            {
                // There is nothing in the database to challenge you,
                // let's give you a doppleganger.
                $badguy = LotgdKernel::get('lotgd_core.tool.creature_functions')->lotgdGenerateDoppelganger($session['user']['level']);
            }
            else
            {
                $badguy = $result[0];
                $badguy = buffbadguy($badguy);
            }

            LotgdKernel::get('lotgd_core.combat.buffer')->calculateBuffFields();
            $badguy['playerstarthp']   = $session['user']['hitpoints'];
            $badguy['diddamage']       = 0;
            $badguy['type']            = 'travel';
            $session['user']['badguy'] = $badguy;
            $battle                    = true;
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
    if ('run' == $op && \mt_rand(1, 5) < 3)
    {
        // They managed to get away.
        \LotgdResponse::pageStart('title.escape', [], 'cities_module');

        $coward = get_module_setting('coward');

        if ($coward)
        {
            modulehook('cities-usetravel',
                [
                    'foresttext' => \LotgdTranslator::t('section.escape.coward.forest', [], 'cities_module'),
                    'traveltext' => \LotgdTranslator::t('section.escape.coward.travel', [], 'cities_module'),
                ]
            );
        }

        $params = [
            'location' => $session['user']['location'],
        ];

        \LotgdNavigation::addNav('navs.enter', ['location' => $session['user']['location']], 'village.php');

        \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/cities/run/escape.twig', $params));

        \LotgdResponse::pageEnd();
    }

    $battle = true;
}
elseif ('' == $op)
{
    \LotgdResponse::pageStart('title.travel', [], 'cities_module');

    \LotgdFlashMessages::addInfoMessage([
        'message' => \LotgdTranslator::t('section.travel.empty', [], 'citites-module'),
        'close'   => false,
    ]);

    \LotgdNavigation::addNav('navs.journey', "runmodule.php?module=cities&op=travel&city={$ccity}&continue=1&d={$danger}");

    module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");

    \LotgdResponse::pageEnd();
}

if ($battle)
{
    \LotgdResponse::pageStart('title.battle', [], 'cities_module');

    /** @var \Lotgd\Core\Combat\Battle */
    $serviceBattle = \LotgdKernel::get('lotgd_core.combat.battle');

    //-- Battle zone.
    $serviceBattle ->initialize()
        ->setBattleZone('city')
        ->disableCreateNews()
        ->battleStart()
        ->battleProcess()
        ->battleEnd()
    ;

    if ($serviceBattle->isVictory())
    {
        \LotgdNavigation::addHeader('common.category.navigation', ['textDomain' => 'navigation_app']);
        \LotgdNavigation::addNav('navs.journey', "runmodule.php?module=cities&op=travel&city={$ccity}&continue=1&d={$danger}");

        module_display_events('travel', "runmodule.php?module=cities&city={$ccity}&d={$danger}&continue=1");
    }
    elseif ($serviceBattle->isDefeat())
    {
        \LotgdLog::addNews('travel.deathmessage', [
            'location' => $city,
            'player'   => $session['user']['name'],
            'creature' => $badbuy['creaturename'],
        ], 'cities_module');
    }
    elseif ( ! $serviceBattle->battleHasWinner())
    {
        $serviceBattle->fightNav(true, true, "runmodule.php?module=cities&city={$ccity}&d={$danger}");
    }

    $serviceBattle->battleResults(); //-- Show results

    \LotgdResponse::pageEnd();
}

//-- Restore text domain for navigation
\LotgdNavigation::setTextDomain();
