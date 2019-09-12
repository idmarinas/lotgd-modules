<?php
/***********************************************
 World Map
 Originally by: Aes
 Updates & Maintenance by: Kevin Hatfield - Arune (khatfield@ecsportal.com)
 Updates & Maintenance by: Roland Lichti - klenkes (klenkes@paladins-inn.de)
 http://www.dragonprime.net
 Updated: Feb 23, 2008
 ************************************************/

$worldmapen_globals = [
    'terrainDefs' => null,
    'map' => null,
    'colors' => null,
];				// global vars as array (only one global var needed)

// -----------------------------------------------------------------------
// BEGIN - FUNCTIONS
// -----------------------------------------------------------------------
// -----------------------------------------------------------------------
// BEGIN - worldmapen_defaultcityloc determines the default city locations
//         for all cities in the game!
// -----------------------------------------------------------------------
function worldmapen_defaultcityloc()
{
    global $session;
    $i = 0;
    $citylocX = 0;
    $citylocY = 0;
    $citylocations = [];
    $citylocations[][] = '';
    $vloc = [];
    $vname = getsetting('villagename', LOCATION_FIELDS);
    $vloc[$vname] = 'village';
    $vloc = modulehook('validlocation', $vloc);

    foreach ($vloc as $loc => $val)
    {
        $k = 0;

        while (0 == $k)
        {
            foreach ($citylocations as $val1)
            {
                if (($val1[0] == $citylocX) && ($val1[1] == $citylocY))
                {
                    $k = 0;

                    $citylocX = e_rand(1, get_module_setting('worldmapsizeX'));
                    $citylocY = e_rand(1, get_module_setting('worldmapsizeY'));
                }
                else
                {
                    $k++;
                    $citylocations[$i][0] = $citylocX;
                    $citylocations[$i][1] = $citylocY;
                    set_module_setting($loc.'X', $citylocX);
                    set_module_setting($loc.'Y', $citylocY);
                    set_module_setting($loc.'Z', '1');
                }
            }
        }
        $i++;
    }
}
// -----------------------------------------------------------------------
// END - worldmapen_defaultcityloc determines the default city locations
//         for all cities in the game!
// -----------------------------------------------------------------------

// -----------------------------------------------------------------------
// END - worldmapen_viewmap allows players to view the world map if they
//         have purchased one from the Gypsy or Item Shop
// -----------------------------------------------------------------------

// -----------------------------------------------------------------------
// BEGIN - worldmapen_determinenav determines in which direction a player
//         may move in the world.  North, East, South, West
// -----------------------------------------------------------------------
function worldmapen_determinenav()
{
    global $session, $nlink, $elink, $wlink, $slink, $nelink, $nwlink, $selink, $swlink;
    $minX = 1;
    $minY = 1;
    $maxX = get_module_setting('worldmapsizeX');
    $maxY = get_module_setting('worldmapsizeY');

    if ($session['user']['superuser'] & ~SU_DOESNT_GIVE_GROTTO)
    {
        \LotgdNavigation::addNav('common.superuser.superuser', 'superuser.php', [ 'textDomain' => 'navigation-app' ]);
    }

    $params = [
        'textDomain' => 'module-worldmapen',
        'campingAllowed' => 1
    ];

    $loc = get_module_pref('worldXYZ');
    $oloc = $loc;
    list($x, $y, $z) = explode(',', $loc);
    $vloc = [];
    $vname = getsetting('villagename', LOCATION_FIELDS);
    $vloc[$vname] = 'village';
    $vloc = modulehook('validlocation', $vloc);

    foreach ($vloc as $loc => $val)
    {
        $cx = get_module_setting($loc.'X');
        $cy = get_module_setting($loc.'Y');
        $cz = get_module_setting($loc.'Z');

        if ($x == $cx && $y == $cy && $z == $cz)
        {
            $session['user']['location'] = $loc;
            set_module_pref('lastCity', '');
            \LotgdNavigation::addHeader('navigation.category.area');
            \LotgdNavigation::addNav('navigation.nav.enter', 'village.php', [ 'params' => [ 'name' => $loc ]]);
            $params['campingAllowed'] = 0;

            break;
        }
    }

    $free = 100;
    if (! get_module_setting('usestamina'))
    {
        $args = modulehook('count-travels', ['available' => 0, 'used' => 0]);
        $free = max(0, $args['available'] - $args['used']);
    }

    $params['tired'] = true;
    $nlink = $elink = $wlink = $slink = $nelink = $nwlink = $selink = $swlink = '#';
    $baseLink = 'runmodule.php?module=worldmapen&op=move&oloc='.rawurlencode($oloc);

    if (0 != $free || $free < 0)
    {
        $params['tired'] = false;

        $plusX = $x + 1;
        $plusY = $y + 1;
        $minusX = $x - 1;
        $minusY = $y - 1;
        $checkN = $plusY <= $maxY;
        $checkE = $plusX <= $maxX;
        $checkS = $minusY >= $minY;
        $checkW = $minusX >= $minX;
        $noTravel = 0; // because they'll never get there and 0 will make sure boundary message is triggered
        // Might be a better way of getting the terrain movement cost for the adjacent squares
        $NterrainCost = $checkN ? worldmapen_terrain_cost($x, $plusY, $z) : $noTravel;
        $NEterrainCost = ($checkN && $checkE) ? worldmapen_terrain_cost($plusX, $plusY, $z) : $noTravel;
        $NWterrainCost = ($checkN && $checkW) ? worldmapen_terrain_cost($minusX, $plusY, $z) : $noTravel;
        $EterrainCost = $checkE ? worldmapen_terrain_cost($plusX, $y, $z) : $noTravel;
        $SterrainCost = $checkS ? worldmapen_terrain_cost($x, $minusY, $z) : $noTravel;
        $SEterrainCost = ($checkS && $checkE) ? worldmapen_terrain_cost($plusX, $minusY, $z) : $noTravel;
        $SWterrainCost = ($checkS && $checkW) ? worldmapen_terrain_cost($minusX, $minusY, $z) : $noTravel;
        $WterrainCost = $checkW ? worldmapen_terrain_cost($minusX, $y, $z) : $noTravel;

        \LotgdNavigation::addHeader('navigation.category.go');

        $params['nBoundary'] = true;
        $nlink = '#';
        if ($y + 1 <= $maxY && $NterrainCost <= $free)
        {
            $params['nBoundary'] = null;
            $nlink = "{$baseLink}&dir=north";
            \LotgdNavigation::addNav('navigation.nav.move.north', $nlink, [ 'params' => [ 'cost' => $NterrainCost ] ]);
        }
        elseif ($NterrainCost > $free)
        {
            $params['nBoundary'] = false;
        }

        $params['eBoundary'] = true;
        $elink = '#';
        if ($x + 1 <= $maxX && $EterrainCost <= $free)
        {
            $params['eBoundary'] = null;
            $elink = "{$baseLink}&dir=east";
            \LotgdNavigation::addNav('navigation.nav.move.east', $elink, [ 'params' => [ 'cost' => $EterrainCost ] ]);
        }
        elseif ($EterrainCost > $free)
        {
            $params['eBoundary'] = false;
        }

        $params['sBoundary'] = true;
        $slink = '#';
        if ($y - 1 >= $minY && $SterrainCost <= $free)
        {
            $params['sBoundary'] = null;
            $slink = "{$baseLink}&dir=south";
            \LotgdNavigation::addNav('navigation.nav.move.south', $slink, [ 'params' => [ 'cost' => $SterrainCost ] ]);
        }
        elseif ($SterrainCost > $free)
        {
            $params['sBoundary'] = false;
        }

        $params['wBoundary'] = true;
        $wlink = '#';
        if ($x - 1 >= $minX && $WterrainCost <= $free)
        {
            $params['wBoundary'] = null;
            $wlink = "{$baseLink}&dir=west";
            \LotgdNavigation::addNav('navigation.nav.move.west', $wlink, [ 'params' => [ 'cost' => $WterrainCost ] ]);
        }
        elseif ($WterrainCost > $free)
        {
            $params['wBoundary'] = false;
        }

        if ('1' == get_module_setting('compasspoints'))
        {
            $nelink = '#';
            if ($y + 1 <= $maxY && $x + 1 <= $maxX && $NEterrainCost <= $free)
            {
                $nelink = "{$baseLink}&dir=northeast";
                \LotgdNavigation::addNav('navigation.nav.move.neast', $nelink, [ 'params' => [ 'cost' => $NEterrainCost ] ]);
            }
            elseif ($NEterrainCost > $free)
            {
                $params['neBoundary'] = false;
            }

            $nwlink = '#';
            if ($y + 1 <= $maxY && $x - 1 >= $minX && $NWterrainCost <= $free)
            {
                $nwlink = "{$baseLink}&dir=northwest";
                \LotgdNavigation::addNav('navigation.nav.move.nwest', $nwlink, [ 'params' => [ 'cost' => $NWterrainCost ] ]);
            }
            elseif ($NWterrainCost > $free)
            {
                $params['nwBoundary'] = false;
            }

            $selink = '#';
            if ($y - 1 >= $minY && $x + 1 <= $maxX && $SEterrainCost <= $free)
            {
                $selink = "{$baseLink}&dir=southeast";
                \LotgdNavigation::addNav('navigation.nav.move.seast', $selink, [ 'params' => [ 'cost' => $SEterrainCost] ]);
            }
            elseif ($SEterrainCost > $free)
            {
                $params['seBoundary'] = false;
            }

            $swlink = '#';
            if ($y - 1 >= $minY && $x - 1 >= $minX && $SWterrainCost <= $free)
            {
                $swlink = "{$baseLink}&dir=southwest";
                \LotgdNavigation::addNav('navigation.nav.move.swest', $swlink, [ 'params' => [ 'cost' => $SWterrainCost] ]);
            }
            elseif ($SWterrainCost > $free)
            {
                $params['swBoundary'] = false;
            }
        }
    }

    if (get_module_setting('turntravel'))
    {
        \LotgdNavigation::addHeader('navigation.category.prolonged');

        if ($session['user']['turns'] > 0 && get_module_setting('turntravel'))
        {
            \LotgdNavigation::addNav('navigation.nav.trade.points', 'runmodule.php?module=worldmapen&op=tradeturn');
        }
    }

    if ($session['user']['superuser'] & SU_EDIT_USERS)
    {
        \LotgdNavigation::addHeader('navigation.category.superuser');

        foreach ($vloc as $loc => $val)
        {
            if ($loc == $session['user']['location'])
            {
                continue;
            }

            \LotgdNavigation::addNav('navigation.nav.go', 'runmodule.php?module=worldmapen&op=destination&cname='.urlencode($loc), [
                'params' => [ 'location' => $loc ]
            ]);
        }

        if (1 == get_module_setting('manualmove'))
        {
            \LotgdNavigation::addHeaderNotl('--');

            if ($y + 1 <= $maxY)
            {
                \LotgdNavigation::addNav('navigation.nav.safe.north', "{$baseLink}&dir=north&su=1");
            }

            if ($x + 1 <= $maxX)
            {
                \LotgdNavigation::addNav('navigation.nav.safe.east', "{$baseLink}&dir=east&su=1");
            }

            if ($y - 1 >= $minY)
            {
                \LotgdNavigation::addNav('navigation.nav.safe.south', "{$baseLink}&dir=south&su=1");
            }

            if ($x - 1 >= $minX)
            {
                \LotgdNavigation::addNav('navigation.nav.safe.west', "{$baseLink}&dir=west&su=1");
            }
        }
    }

    if ($session['user']['superuser'] & SU_INFINITE_DAYS)
    {
        \LotgdNavigation::addHeader('navigation.category.superuser');
        \LotgdNavigation::addNav('common.superuser.newday', 'newday.php', [ 'textDomain' => 'navigation-app' ]);
    }

    if (get_module_pref('worldmapbuy') || ($session['user']['superuser'] & SU_EDIT_USERS))
    {
        \LotgdNavigation::addHeader('navigation.category.map');
        LotgdNavigation::addNav('navigation.nav.map', 'runmodule.php?module=worldmapen&op=viewmap');
    }

    if ($params['campingAllowed'])
    {
        \LotgdNavigation::addHeader('navigation.category.quit');
        \LotgdNavigation::addNav('navigation.nav.camp', 'runmodule.php?module=worldmapen&op=camp');
    }

    //cmj edit: worldnav now passes user location in args, to save getting the modulepref again in modules that query the user's location
    $hook = [
        'x' => $x,
        'y' => $y,
        'z' => $z
    ];
    $hook = modulehook('worldnav', $hook);

    rawoutput(\LotgdTheme::renderModuleTemplate('worldmapen/lib/determinenav.twig', $params));

    return [
        'nLink' => $nlink,
        'eLink' => $elink,
        'wLink' => $wlink,
        'sLink' => $slink,
        'neLink' => $nelink,
        'nwLink' => $nwlink,
        'seLink' => $selink,
        'swLink' => $swlink,
        'campingAllowed' => $params['campingAllowed']
    ];
}
// -----------------------------------------------------------------------
// END - worldmapen_determinenav determines in which direction a player
//         may move in the world.  North, East, South, West
// -----------------------------------------------------------------------

function worldmapen_terrain_cost($x, $y, $z = 1)
{
    $terrain = worldmapen_getTerrain($x, $y, $z);
    $terrains = worldmapen_loadTerrainDefs();

    return $terrains[$terrain['type']]['moveCost'];
}

//Function to interact with Expanded Stamina System, added by Caveman Joe
function worldmapen_terrain_takestamina($x, $y, $z = 1)
{
    global $session;

    if (! get_module_setting('usestamina', 'worldmapen'))
    {
        return;
    }

    require_once 'modules/staminasystem/lib/lib.php';
    $terrain = worldmapen_getTerrain($x, $y, $z);

    switch ($terrain['type'])
    {
        case 'plains':
            $plains = process_action('Travelling - Plains');

            if ($plains['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across plains!  You are now level %s!  This action will cost fewer Stamina points now, so you can saunter across more fields per day.`b`c`n', $plains['lvlinfo']['newlvl']);
            }
        break;
        case 'river':
            $river = process_action('Travelling - River');

            if ($river['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across rivers!  You are now level %s!  This action will cost fewer Stamina points now, so you can wade through more rivers in a single day.`b`c`n', $river['lvlinfo']['newlvl']);
            }
        break;
        case 'ocean':
            $ocean = process_action('Travelling - Ocean');

            if ($ocean['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across deep water!  You are now level %s!  This action will cost fewer Stamina points now, so you can swim more in a single day.`b`c`n', $ocean['lvlinfo']['newlvl']);
            }
        break;
        case 'earth':
            $earth = process_action('Travelling - Earth');

            if ($earth['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across earth!  You are now level %s!  This action will cost fewer Stamina points now, so you can saunter across more beachy terrain in a single day.`b`c`n', $earth['lvlinfo']['newlvl']);
            }
        break;
        case 'air':
            $air = process_action('Travelling - Air');

            if ($air['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across air!  You are now level %s!  This action will cost fewer Stamina points now, so you can fly through for air in a single day.`b`c`n', $air['lvlinfo']['newlvl']);
            }
        break;
        case 'desert':
            $desert = process_action('Travelling - Desert');

            if ($desert['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across deserts!  You are now level %s!  This action will cost fewer Stamina points now, so you can saunter across more beachy terrain in a single day.`b`c`n', $desert['lvlinfo']['newlvl']);
            }
            break;
        case 'swamp':
            $swamp = process_action('Travelling - Swamp');

            if ($swamp['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across swamps!  You are now level %s!  This action will cost fewer Stamina points now, so you can wade through more swampy goo every day.`b`c`n', $swamp['lvlinfo']['newlvl']);
            }
            break;
        case 'mountains':
            $mount = process_action('Travelling - Mountains');

            if ($mount['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across mountains!  You are now level %s!  This action will cost fewer Stamina points now, so you can scale more mountains in a single day.`b`c`n', $mount['lvlinfo']['newlvl']);
            }
            break;
        case 'snow':
            $snow = process_action('Travelling - Snow');

            if ($snow['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across snow!  You are now level %s!  This action will cost fewer Stamina points now, so you can stomp through more snow in a single day.`b`c`n', $snow['lvlinfo']['newlvl']);
            }
        break;
        case 'forest':
        default:
            $forest = process_action('Travelling - Forest');

            if ($forest['lvlinfo']['levelledup'])
            {
                output('`n`c`b`0You gained a level in Travel across dense jungle!  You are now level %s!  This action will cost fewer Stamina points now, so you can navigate more thick jungle terrain per day.`b`c`n', $forest['lvlinfo']['newlvl']);
            }
        break;
    }
}

function worldmapen_encounter($x, $y, $z = 1)
{
    global $session;

    $terrain = worldmapen_getTerrain($x, $y, $z);
    $id = $session['user']['hashorse'];

    if (0 != $id)
    {
        switch ($terrain['type'])
        {
            case 'plains':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterPlains');
            break;
            case 'river':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterRiver');
            break;
            case 'ocean':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterOcean');
            break;
            case 'desert':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterDesert');
            break;
            case 'swamp':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterSwamp');
            break;
            case 'mountains':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterMountains');
            break;
            case 'snow':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterSnow');
            break;
            case 'earth':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterEarth');
            break;
            case 'air':
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterAir');
            break;
            case 'forest':
            default:
                $terrain['encounter'] = $terrain['encounter'] * get_module_objpref('mounts', $id, 'encounterForest');
            break;
        }
    }
    //Interaction with Stamina system - increases encounter rate by 1% for every percentage point of player's Amber stamina used
    if (get_module_setting('usestamina'))
    {
        require_once 'modules/staminasystem/lib/lib.php';
        $amber = get_stamina();

        if ($amber < 100)
        {
            \LotgdFlashMessages::addWarningMessage('flash.message.encounter.tired', [], 'module-worldmapen');
        }
        $add = 100 - $amber;
        $terrain['encounter'] += $add;
    }

    return $terrain['encounter'];
}

function worldmapen_getColorDefinitions()
{
    $def = worldmapen_loadTerrainDefs();

    $retValue = [];

    foreach ($def as $key => $value)
    {
        $retValue[$key] = $value['color'];
    }

    return $retValue;
}

/**
 * Get terrain info.
 *
 * @param int $x
 * @param int $y
 * @param int $z
 *
 * @return array
 */
function worldmapen_getTerrain($x, $y, $z)
{
    global $worldmapen_globals;

    $terrains = worldmapen_loadTerrainDefs();
    $map = worldmapen_loadMap($z);

    $terrainType = $map[$x][$y];

    return $terrains[$terrainType];
}

function worldmapen_setTerrain($map, $x, $y, $z = 1, $type = 'forest')
{
    //	debug("x={$x}, y={$y}, z={$z}, type={$type}");

    if (is_numeric($type))
    {
        switch ($type)
        {
            case 0: $type = 'plains'; break;
            case 1: $type = 'forest'; break;
            case 2: $type = 'river'; break;
            case 3: $type = 'ocean'; break;
            case 4: $type = 'desert'; break;
            case 5: $type = 'swamp'; break;
            case 6: $type = 'mountains'; break;
            case 7: $type = 'snow'; break;
            case 8: $type = 'earth'; break;
            case 9: $type = 'air'; break;

            default:
                debug("Invalid terrain type '{$type}'. Setting to 'forest'.");
                $type = 'forest';
            break;
        }
    }

    if ($map[$z][$x][$y] != $type)
    {
        debug("Changing type of ({$x}, {$y}) from '{$map[$z][$x][$y]}' to '{$type}'.");

        $map[$z][$x][$y] = $type;
    }

    return $map;
}

function worldmapen_loadTerrainDefs(): array
{
    $useStamina = false;
    if (get_module_setting('usestamina'))
    {
        require_once 'modules/staminasystem/lib/lib.php';

        $useStamina = true;
    }

    $terrains = datacache('module-worldmapen-terrain-defs', 86400, true);

    if (! is_array($terrains) || ! $terrains)
    {
        $terrains = [
            'plains' => [
                'type' => 'plains',
                'color' => get_module_setting('colorPlains'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Plains') : get_module_setting('moveCostPlains'),
                'encounter' => get_module_setting('encounterPlains')],
            'forest' => [
                'type' => 'forest',
                'color' => get_module_setting('colorForest'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Forest') : get_module_setting('moveCostForest'),
                'encounter' => get_module_setting('encounterForest')],
            'river' => [
                'type' => 'river',
                'color' => get_module_setting('colorRiver'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - River') : get_module_setting('moveCostRiver'),
                'encounter' => get_module_setting('encounterRiver')],
            'ocean' => [
                'type' => 'ocean',
                'color' => get_module_setting('colorOcean'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Ocean') : get_module_setting('moveCostOcean'),
                'encounter' => get_module_setting('encounterOcean')],
            'desert' => [
                'type' => 'desert',
                'color' => get_module_setting('colorDesert'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Desert') : get_module_setting('moveCostDesert'),
                'encounter' => get_module_setting('encounterDesert')],
            'swamp' => [
                'type' => 'swamp',
                'color' => get_module_setting('colorSwamp'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Swamp') : get_module_setting('moveCostSwamp'),
                'encounter' => get_module_setting('encounterSwamp')],
            'mountains' => [
                'type' => 'mountains',
                'color' => get_module_setting('colorMountains'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Mountains') : get_module_setting('moveCostMountains'),
                'encounter' => get_module_setting('encounterMountains')],
            'snow' => [
                'type' => 'snow',
                'color' => get_module_setting('colorSnow'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Snow') : get_module_setting('moveCostSnow'),
                'encounter' => get_module_setting('encounterSnow')],
            'earth' => [
                'type' => 'earth',
                'color' => get_module_setting('colorEarth'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Earth') : get_module_setting('moveCostEarth'),
                'encounter' => get_module_setting('encounterEarth')],
            'air' => [
                'type' => 'air',
                'color' => get_module_setting('colorAir'),
                'moveCost' => $useStamina ? stamina_getdisplaycost('Travelling - Air') : get_module_setting('moveCostAir'),
                'encounter' => get_module_setting('encounterAir')],
        ];

        updatedatacache('module-worldmapen-terrain-defs', $terrains, true);
    }

    return $terrains;
}

/**
 * Load map.
 *
 * @param int|null $z
 *
 * @return array
 */
function worldmapen_loadMap(?int $z = 1)
{
    $terrains = datacache('module-worldmapen-terrain-map', 86400, true);

    if (! is_array($terrains) || ! $terrains)
    {
        $map = get_module_setting('TerrainDefinition', 'worldmapen');

        if ($map)
        {
            $terrains = unserialize($map);
        }
        else
        {
            $terrains = worldmapen_generateNewMap('forest');
            worldmapen_saveMap($terrains);
        }

        updatedatacache('module-worldmapen-terrain-map', $terrains, true);
    }

    return $terrains[$z] ?? $terrains;
}

/**
 * Saved map.
 *
 * @return void
 */
function worldmapen_saveMap(array $map)
{
    if (empty($map))
    {
        debug("Sorry, no map defined until now. Can't save a nonexisting map. Will generate a new world.");

        $map = worldmapen_generateNewMap();
    }

    set_module_setting('TerrainDefinition', serialize($map));

    invalidatedatacache('module-worldmapen-terrain-map', true);
    invalidatedatacache('module-worldmapen-terrain-defs', true);
}

function worldmapen_generateNewMap($defaultTerrain = 'forest')
{
    $retValue = [];

    $terrains = worldmapen_loadTerrainDefs();

    if (! array_key_exists($defaultTerrain, $terrains))
    {
        debug("Invalid terrain type '{$defaultTerrain}'. Using 'forest' instead.");
        $defaultTerrain = 'forest';
    }

    // Level 0 will be "Earth", level 2 will be "Air" until otherwise defined.

    $maxX = get_module_setting('worldmapsizeX', 'worldmapen');
    $maxY = get_module_setting('worldmapsizeY', 'worldmapen');

    for ($x = 1; $x <= $maxX; $x++)
    {
        for ($y = 1; $y <= $maxY; $y++)
        {
            $retValue[0][$x][$y] = 'earth';
            $retValue[1][$x][$y] = $defaultTerrain;
            $retValue[2][$x][$y] = 'air';
        }
    }

    debug("Map with size {$maxX} x {$maxY} generated with default '{$defaultTerrain}'.");

    return $retValue;
}
