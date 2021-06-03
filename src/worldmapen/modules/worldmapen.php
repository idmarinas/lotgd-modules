<?php
/*
    World Map
    Originally by: Aes
    Updates & Maintenance by: Kevin Hatfield - Arune (khatfield@ecsportal.com)
    Updates & Maintenance by: Roland Lichti - klenkes (klenkes@paladins-inn.de)
    Updates & Maintenance by: Dan Hall - Caveman Joe (cavemanjoe@gmail.com)
    http://www.dragonprime.net
    Updated: Feb 23, 2008
 */

function worldmapen_getmoduleinfo()
{
    return [
        'name'      => 'World Map',
        'version'   => '1.3.0',
        'author'    => 'Originally: AES and Kevin Hatfield, Maintained by Roland Lichti, Stamina and Mount interaction added by Caveman Joe, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category'  => 'Map',
        'download'  => 'https://github.com/idmarinas/lotgd-modules',
        'vertxtloc' => 'http://www.dragonprime.net/users/klenkes/',
        'requires'  => [
            'lotgd' => '>=5.0.0|Need a version equal or greater than 5.0.0 IDMarinas Edition',
            'cities' => '>=2.0.0|This module requires the Multiple Cities module to be installed',
        ],
        'settings' => [
            'World Map Settings,title',
            'worldmapsizeX'    => 'How wide is the world? (X),int|5',
            'worldmapsizeY'    => 'How long is the world? (Y),int|5',
            'extraTravels'     => 'How many additional travels are they given per day,int|5',
            'manualmove'       => 'Turn on Superuser manual movement?,bool|0',
            'viewRadius'       => 'How many squares far can a player see while traveling?,range,0,10,2',
            'worldmapAcquire'  => 'Can the world map be purchased?,bool|1',
            'worldmapCostGold' => 'How much gold does the World Map cost?,int|10000',
            'enableTerrains'   => 'Enable Terrains?,bool|1',
            'showcompass'      => 'Show images/compass.png?,bool|0',
            'compasspoints'    => '8 point compass?,bool|0',
            'showcities'       => 'Show the cities in the key? / Will show all cities,bool|0',
            'smallmap'         => 'Show small map?,bool|1',
            'showforestnav'    => 'Show the forest link in village?,bool|0',
            'wmspecialchance'  => 'Chance for a special during travel,int|7',
            'randchance'       => 'Percent chance you will get a travel module instead of forest,range,5,100,5',

            'Turns and Stamina,title',
            'useturns'       => "Use one of the player's Turns when they encounter a monster?,bool|0",
            'allowzeroturns' => "Allow the fight to go ahead if the player's Turns are zero?,bool|1",
            'turntravel'     => 'Allow the player to trade one of his Turns for this many Travel points (set to zero to disable),int|0',
            'usestamina'     => 'Expanded Stamina system is installed and should be used instead of Travel points,bool|0',

            'Visual Map Settings,title',
            'colorUserLoc'   => 'What color background is the users current location?,text|#FF9900',
            'colorPlains'    => 'Color of plains? (default tile),text|#90EE90',
            'colorForest'    => 'Color of dense forest?,text|#228B22',
            'colorRiver'     => 'Color of river?,text|#4169E1',
            'colorOcean'     => 'Color of ocean?,text|#0000CD',
            'colorDesert'    => 'Color of desert?,text|#DDDD33',
            'colorSwamp'     => 'Color of swamp?,text|#808000',
            'colorMountains' => 'Color of mountains?,text|#A9A9A9',
            'colorSnow'      => 'Color of Snow?,text|#FFFFFF',
            'colorEarth'     => 'Color of Earth?,text|#8B4513',
            'colorAir'       => 'Color of Air?,text|#BDE4FC',
            'All colors must be in HEX format.,note',

            'Terrain Encounter Settings,title',
            'encounterPlains'    => 'Chance of encountering a monster when crossing plains?,int|20',
            'encounterForest'    => 'Chance of encountering a monster when crossing dense forests?,int|85',
            'encounterRiver'     => 'Chance of encountering a monster when crossing rivers?,int|20',
            'encounterOcean'     => 'Chance of encountering a monster when crossing oceans?,int|20',
            'encounterDesert'    => 'Chance of encountering a monster when crossing deserts?,int|85',
            'encounterSwamp'     => 'Chance of encountering a monster when crossing swamps?,int|85',
            'encounterMountains' => 'Chance of encountering a monster when crossing mountains?,int|20',
            'encounterSnow'      => 'Chance of encountering a monster when crossing snow?,int|20',
            'encounterEarth'     => 'Chance of encountering a monster when under surface?,int|1',
            'encounterAir'       => 'Chance of encountering a monster when traveling in the air?,int|0',

            'Terrain Settings,title',
            'moveCostPlains'    => 'Movement cost for crossing plains?,int|1',
            'moveCostForest'    => 'Movement cost for crossing dense forests?,int|1',
            'moveCostRiver'     => 'Movement cost for crossing rivers?,int|1',
            'moveCostOcean'     => 'Movement cost for crossing oceans?,int|5',
            'moveCostDesert'    => 'Movement cost for crossing deserts?,int|2',
            'moveCostSwamp'     => 'Movement cost for crossing swamps?,int|2',
            'moveCostMountains' => 'Movement cost for crossing mountains?,int|3',
            'moveCostSnow'      => 'Movement cost for crossing snow?,int|3',
            'moveCostEarth'     => 'Movement costs for crossing earth?,int|1000',
            'moveCostAir'       => 'Movement costs for crossing air?,int|1000',
        ],
        'prefs' => [
            'World Map User Preferences,title',
            'worldXYZ'        => 'World Map X Y Z (separated by commas!)|0,0,0',
            'canedit'         => 'Does user have rights to edit the map?,bool|0',
            'lastCity'        => 'Where did the user leave from last?|',
            'worldmapbuy'     => 'Did user buy map?,bool|0',
            'encounterchance' => "Player's encounter chance expressed as a percentage of normal,int|100",
            'fuel'            => 'The reduced-cost moves that a player has left because of his Mount,int|0',
        ],
        'prefs-mounts' => [
            'World Map Mount Preferences,title',
            'All values are expressed as a decimal value of normal,note',
            'encounterPlains'    => 'Encounter rate for crossing plains?,float|1',
            'encounterForest'    => 'Encounter rate for crossing dense forests?,float|1',
            'encounterRiver'     => 'Encounter rate for crossing rivers?,float|1',
            'encounterOcean'     => 'Encounter rate for crossing oceans?,float|1',
            'encounterDesert'    => 'Encounter rate for crossing deserts?,float|1',
            'encounterSwamp'     => 'Encounter rate for crossing swamps?,float|1',
            'encounterMountains' => 'Encounter rate for crossing mountains?,float|1',
            'encounterSnow'      => 'Encounter rate for crossing snow?,float|1',
            'encounterEarth'     => 'Encounter rate for crossing earth?,float|1',
            'encounterAir'       => 'Encounter rates for crossing air?,float|1',
        ],
    ];
}

function worldmapen_install()
{
    module_addhook('village');
    module_addhook('villagenav');
    module_addhook('mundanenav');
    module_addhook('superuser');
    module_addhook('pvpcount');
    module_addhook('count-travels');
    module_addhook('changesetting');
    module_addhook('boughtmount');
    module_addhook('newday');
    module_addhook('items-returnlinks');
    module_addhook('everyfooter');

    if (is_module_installed('staminasystem'))
    {
        require_once 'modules/staminasystem/lib/lib.php';

        install_action('Travelling - Plains', [
            'maxcost'       => 5000,
            'mincost'       => 2500,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 25,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - Forest', [
            'maxcost'       => 10000,
            'mincost'       => 4000,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 60,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - River', [
            'maxcost'       => 15000,
            'mincost'       => 5000,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 100,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - Ocean', [
            'maxcost'       => 25000,
            'mincost'       => 7500,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 175,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - Mountains', [
            'maxcost'       => 20000,
            'mincost'       => 6000,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 140,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - Snow', [
            'maxcost'       => 25000,
            'mincost'       => 7500,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 175,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - Earth', [
            'maxcost'       => 5000,
            'mincost'       => 2500,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 25,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - Swamp', [
            'maxcost'       => 12500,
            'mincost'       => 5000,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 75,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - Air', [
            'maxcost'       => 30000,
            'mincost'       => 7500,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 180,
            'class'         => 'Travelling',
        ]);
        install_action('Travelling - Desert', [
            'maxcost'       => 25000,
            'mincost'       => 7500,
            'firstlvlexp'   => 500,
            'expincrement'  => 1.1,
            'costreduction' => 175,
            'class'         => 'Travelling',
        ]);
    }

    //-- Upgraded map to new version
    if ( ! get_module_setting('mapUpgraded', 'worldmapen'))
    {
        require_once 'modules/worldmapen/lib.php';

        worldmapen_upgrade_map();

        set_module_setting('mapUpgraded', 1, 'worldmapen');
    }

    return true;
}

function worldmapen_uninstall()
{
    if (is_module_installed('staminasystem'))
    {
        require_once 'modules/staminasystem/lib/lib.php';

        uninstall_action('Travelling - Plains');
        uninstall_action('Travelling - Forest');
        uninstall_action('Travelling - River');
        uninstall_action('Travelling - Ocean');
        uninstall_action('Travelling - Mountains');
        uninstall_action('Travelling - Snow');
        uninstall_action('Travelling - Earth');
        uninstall_action('Travelling - Swamp');
        uninstall_action('Travelling - Air');
        uninstall_action('Travelling - Desert');
    }

    return true;
}

function worldmapen_run()
{
    require_once 'modules/worldmapen/lib.php';
    require_once 'modules/worldmapen/run.php';

    $textDomain = 'module_worldmapen';
    $op         = (string) \LotgdRequest::getQuery('op');

    //-- Change text domain for navigation
    \LotgdNavigation::setTextDomain('module_worldmapen');

    //-- Handle the admin editor first
    if ('edit' == $op)
    {
        if ( ! get_module_pref('canedit'))
        {
            check_su_access(SU_EDIT_USERS);
        }

        if (1 != get_module_setting('worldmapenInstalled'))
        {
            set_module_setting('worldmapenInstalled', '1');
            worldmapen_defaultcityloc();
        }

        return worldmapen_editor();
    }

    if ( ! get_module_setting('worldmapenInstalled'))
    {
        \LotgdResponse::pageStart('title.not.installed', [], $textDomain);

        \LotgdNavigation::villageNav();

        \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/worldmapen/run/not-installed.twig', [
            'textDomain' => $textDomain,
        ]));

        \LotgdResponse::pageEnd();
    }

    worldmapen_run_real();

    //-- Restore text domain for navigation
    \LotgdNavigation::setTextDomain();
}

function worldmapen_editor()
{
    require_once 'modules/worldmapen/lib.php';
    require_once 'modules/worldmapen/editor.php';

    return worldmapen_editor_real();
}

function worldmapen_dohook($hookname, $args)
{
    global $session;

    require_once 'modules/worldmapen/lib.php';

    // If the cities module is deactivated, we do nothing.
    if ( ! is_module_active('cities'))
    {
        return $args;
    }

    //-- Change text domain for navigation
    \LotgdNavigation::setTextDomain('module_worldmapen');

    if (\file_exists("modules/worldmapen/dohook/{$hookname}.php"))
    {
        require "modules/worldmapen/dohook/{$hookname}.php";
    }
    else
    {
        \LotgdResponse::pageDebug("Sorry, I don't have the hook '{$hookname}' programmed.");
    }

    //-- Restore text domain for navigation
    \LotgdNavigation::setTextDomain();

    return $args;
}
