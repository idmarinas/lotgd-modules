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
        'name'     => 'New Day Bar',
        'version'  => '4.0.0',
        'author'   => 'Joshua Ecklund, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'download' => 'http://dragonprime.net/users/mProwler/newdaybar.zip',
        'category' => 'Stat Display',
        'settings' => [
            'New Day Bar Module Settings,title',
            'showtime' => 'Show time to new day,bool|1',
            'showbar'  => 'Show time as a bar,bool|1',
        ],
        'requires' => [
            'lotgd' => '>=7.0.0|Need a version equal or greater than 7.0.0 IDMarinas Edition',
        ],
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
        $showbar  = get_module_setting('showbar');

        if ( ! $showtime && ! $showbar)
        {
            return $args;
        }

        $details      = LotgdKernel::get("lotgd_core.tool.date_time")->gameTimeDetails();
        $secstonewday = LotgdKernel::get('lotgd_core.tool.date_time')->secondsToNextGameDay($details);

        $newdaypct = \round($details['realsecstotomorrow'] / $details['secsperday'] * 100, 4);
        $newdaypct = \min(100, \max(0, $newdaypct));

        $newdaytxt = \date('G:i:s', $secstonewday);
        $newdaytxt = '<div id="module-newdaybar-timer" class="absolute h-5 w-full text-center text-lotgd-green-300">'.$newdaytxt.'</div>';

        if ( ! $showtime)
        {
            $newdaytxt = '';
        }

        $bar = $newdaytxt;

        if ($showbar)
        {
            $bar = "<div class='h-5 w-full bg-gray-500 relative rounded overflow-hidden'>
                <div id='module-newdaybar-progress' class='absolute h-5 bg-green-600'></div>"
            ;

            $bar .= $newdaytxt ?: '';

            $bar .= '</div>';
        }

        LotgdResponse::pageAddContent(LotgdTheme::render('@module/newdaybar_charstats.twig', [
            'realSecsToTomorrow' => $details['realsecstotomorrow'],
            'secsPerDay'         => $details['secsperday'],
            'secsToNewDay'       => $secstonewday,
            'showTime'           => $showtime,
        ]));

        LotgdKernel::get("Lotgd\Core\Character\Stats")->setcharstat(
            LotgdTranslator::t('statistic.category.character.extra', [], 'app_default'),
            LotgdTranslator::t('charstats.stat', [], 'module_newdaybar'),
            $bar
        );
    }

    return $args;
}

function newdaybar_run()
{
}
