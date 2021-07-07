<?php

// translator ready
// addnews ready
// mail ready

/* gardener */
/* ver 1.0 by Shannon Brown => SaucyWench -at- gmail -dot- com */
/* 3rd December 2004 */

function gardener_getmoduleinfo()
{
    return [
        'name'     => 'Gardener',
        'version'  => '3.0.0',
        'author'   => 'Shannon Brown, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Gardens',
        'download' => 'core_module',
        'settings' => [
            'Gardener - Settings,title',
            'customtext'  => 'Custom message for server,textarea|',
            'gardens'     => 'Does the gazebo appear in the gardens? (setting yes nullifies city selector below),bool|0',
            'gardenerloc' => 'In which city does the gazebo appear,location|'.LotgdSetting::getSetting('villagename', LOCATION_FIELDS),
        ],
        'prefs' => [
            'Gardener - User Preferences,title',
            'seentoday' => 'Has the player visited today?,bool|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function gardener_install()
{
    module_addhook('gardens');
    module_addhook('changesetting');
    module_addhook('village-desc');
    module_addhook('newday');
    module_addhook('footer-runmodule');

    return true;
}

function gardener_uninstall()
{
    return true;
}

function gardener_dohook($hookname, $args)
{
    global $session;

    $gardens = get_module_setting('gardens');

    switch ($hookname)
    {
        case 'changesetting':
            if ( ! $gardens && 'villagename' == $args['setting'] && $args['old'] == get_module_setting('gardenerloc'))
            {
                set_module_setting('gardenerloc', $args['new']);
            }
        break;
        case 'gardens':
            if ($gardens)
            {
                \LotgdNavigation::addNavNotl('Gazebo', 'runmodule.php?module=gardener');

                $customtext = get_module_setting('customtext');
                \LotgdResponse::pageAddContent(\LotgdFormat::colorize(\sprintf('`n`%%s`0', $customtext), true));
            }
        break;
        case 'footer-runmodule':
            if ('newbieisland' != \LotgdRequest::getQuery('module'))
            {
                break;
            }
        break;
        case 'village-desc':
            if ( ! $gardens && $session['user']['location'] == get_module_setting('gardenerloc'))
            {
                \LotgdNavigation::addHeader('headers.market');
                \LotgdNavigation::addNavNotl('Gazebo', 'runmodule.php?module=gardener');

                $customtext = get_module_setting('customtext');

                if ($customtext)
                {
                    $args[] = \sprintf('`n`n`c`@%s`0´c', $customtext);
                }
            }
        break;
        case 'newday':
            set_module_pref('seentoday', 0);
        break;
    }

    return $args;
}

function gardener_run()
{
    global $session;

    $op        = \LotgdRequest::getQuery('op');
    $seentoday = get_module_pref('seentoday');
    $gardens   = get_module_setting('gardens');

    $textDomain = 'module_gardener';

    \LotgdResponse::pageStart('title', [], $textDomain);

    $params = [
        'textDomain' => $textDomain,
        'gardens'    => $gardens,
    ];

    \LotgdNavigation::setTextDomain($textDomain);

    if ($seentoday)
    {
        $params['tpl'] = 'seen';

        if ($gardens)
        {
            \LotgdNavigation::addNav('navigation.nav.return.garden', 'gardens.php');
        }
        else
        {
            \LotgdNavigation::villageNav();
        }
    }
    elseif ('' == $op)
    {
        $params['tpl'] = 'enter';

        \LotgdNavigation::addNav('navigation.nav.try', 'runmodule.php?module=gardener&op=ask');

        if ($gardens)
        {
            \LotgdNavigation::addNav('navigation.nav.return.garden', 'gardens.php');
        }
        else
        {
            \LotgdNavigation::villageNav();
        }
    }
    elseif ('ask' == $op)
    {
        $params['tpl'] = 'ask';

        // The questions have their expected answer associated with them
        // now, so you can change questions at your whim as long as you
        // put the correct answers there.
        $phrases = [
            \LotgdTranslator::t('question.00', [], $textDomain).'|0',
            \LotgdTranslator::t('question.01', [], $textDomain).'|0',
            \LotgdTranslator::t('question.02', [], $textDomain).'|0',
            \LotgdTranslator::t('question.03', [], $textDomain).'|0',
            \LotgdTranslator::t('question.04', [], $textDomain).'|0',
            \LotgdTranslator::t('question.05', [], $textDomain).'|0',
            \LotgdTranslator::t('question.06', [], $textDomain).'|0',
            \LotgdTranslator::t('question.07', [], $textDomain).'|0',
            \LotgdTranslator::t('question.08', [], $textDomain).'|0',
            \LotgdTranslator::t('question.09', [], $textDomain).'|0',
            \LotgdTranslator::t('question.010', [], $textDomain).'|0',
            \LotgdTranslator::t('question.011', [], $textDomain).'|0',
            \LotgdTranslator::t('question.012', [], $textDomain).'|0',
            \LotgdTranslator::t('question.013', [], $textDomain).'|0',
            \LotgdTranslator::t('question.014', [], $textDomain).'|1',
            \LotgdTranslator::t('question.015', [], $textDomain).'|1',
            \LotgdTranslator::t('question.016', [], $textDomain).'|1',
            \LotgdTranslator::t('question.017', [], $textDomain).'|1',
            \LotgdTranslator::t('question.018', [], $textDomain).'|1',
            \LotgdTranslator::t('question.019', [], $textDomain).'|1',
        ];

        $question    = \array_rand($phrases);
        $myphrase    = $phrases[$question];
        list($q, $a) = \explode('|', $myphrase);
        set_module_pref('expectanswer', $a);

        $params['question'] = $q;

        \LotgdNavigation::addNav('navigation.nav.ask.yes', 'runmodule.php?module=gardener&op=answer&val=1');
        \LotgdNavigation::addNav('navigation.nav.ask.no', 'runmodule.php?module=gardener&op=answer&val=0');
    }
    else
    {
        $params['tpl'] = 'answer';

        $val = (int) \LotgdRequest::getQuery('val');

        // Did we get it wrong?
        if ($val != get_module_pref('expectanswer'))
        {
            // bad result
            $params['correct'] = false;
        }
        else
        {
            // answer is correct
            $params['correct'] = true;

            $gift = \mt_rand(1, 7);

            if (7 == $gift)
            {
                $params['reward'] = 0;
                ++$session['user']['gems'];
            }
            else
            {
                $vargold = \mt_rand(0, 20);
                $addgold = $vargold + (\round(\max(10, (200 - $session['user']['dragonkills'])) * 0.1) * $session['user']['level']);
                $session['user']['gold'] += $addgold;

                $params['reward'] = $addgold;
            }
        }

        set_module_pref('seentoday', 1);

        // And the correct return link(s)
        if ($gardens)
        {
            \LotgdNavigation::addNav('navigation.nav.return.garden', 'gardens.php');
        }
        \LotgdNavigation::villageNav();
    }

    \LotgdNavigation::setTextDomain();

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/gardener/run.twig', $params));

    \LotgdResponse::pageEnd();
}
