<?php

/**
 * Title:       New Day Bar
 * Date:	Sep 06, 2004
 * Version:	1.2
 * Author:      Joshua Ecklund
 * Email:       m.prowler@cox.net
 * Purpose:     Add a countdown timer for the new day to "Personal Info"
 *              status bar.
 *
 * --Change Log--
 *
 * Date:    	Jul 30, 2004
 * Version:	1.0
 * Purpose:     Initial Release
 *
 * Date:        Aug 01, 2004
 * Version:     1.1
 * Purpose:     Various changes/fixes suggested by JT Traub (jtraub@dragoncat.net)
 *
 * Date:        Sep 06, 2004
 * Version:     1.2
 * Purpose:     Updated to use functions included in 0.9.8-prerelease.3
 */
function newdaybar_getmoduleinfo()
{
    return [
        'name' => 'New Day Bar',
        'version' => '2.0.0',
        'author' => 'Joshua Ecklund, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'download' => 'http://dragonprime.net/users/mProwler/newdaybar.zip',
        'category' => 'Stat Display',
        'settings' => [
            'New Day Bar Module Settings,title',
            'showtime' => 'Show time to new day,bool|1',
            'showbar' => 'Show time as a bar,bool|1',
        ],
        'requires' => [
            'lotgd' => '>=4.0.0|Need a version equal or greater than 4.0.0 IDMarinas Edition'
        ]
    ];
}

function newdaybar_install()
{
    module_addhook('charstats');

    return true;
}

function newdaybar_uninstall()
{
    return true;
}

function newdaybar_dohook($hookname, $args)
{
    global $session;

    if ('charstats' == $hookname)
    {
        $showtime = get_module_setting('showtime');
        $showbar = get_module_setting('showbar');

        if (! $showtime && ! $showbar)
        {
            return $args;
        }

        require_once 'lib/datetime.php';

        $details = gametimedetails();
        $secstonewday = secondstonextgameday($details);

        $newdaypct = round($details['realsecstotomorrow'] / $details['secsperday'] * 100, 4);
        $newdaypct = min(100, max(0, $newdaypct));

        $newdaytxt = date('G:i:s', $secstonewday);
        $newdaytxt = '<div class="newdaybar timer label">`@'.$newdaytxt.'`0</div>';

        if (! $showtime)
        {
            $newdaytxt = '';
        }

        $bar = $newdaytxt;

        if ($showbar)
        {
            $bar = "<div class='ui tiny indicating lotgd progress ".($newdaytxt ? '' : 'remove margin')." newdaybar' data-total='{$details['secsperday']}' data-value='{$details['realsecstotomorrow']}'><div class='bar'></div>";

            $bar .= $newdaytxt ?: '';

            $bar .= '</div>';
        }

        \LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('newdaybar/dohook/charstats.twig', [
            'realSecsToTomorrow' => $details['realsecstotomorrow'],
            'secsPerDay' => $details['secsperday'],
            'secsToNewDay' => $secstonewday,
            'showTime' => $showtime
        ]));

        setcharstat(
            \LotgdTranslator::t('statistic.category.character.extra', [], 'app-default'),
            \LotgdTranslator::t('charstats.stat', [], 'module-newdaybar'),
            $bar
        );
    }

    return $args;
}

function newdaybar_run()
{
}
