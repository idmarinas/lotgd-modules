<?php

namespace Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen;

use Twig\Environment;

trait Legend
{
    /**
     * World map key (Legend).
     *
     * @param bool $showloc
     */
    public function showLegend(Environment $env, $showloc): string
    {
        $vloc         = [];
        $vname        = $this->settings->getSetting('villagename', LOCATION_FIELDS);
        $vloc[$vname] = 'village';
        $vloc         = modulehook('validlocation', $vloc);

        $loc                                     = get_module_pref('worldXYZ', 'worldmapen');
        list($worldmapX, $worldmapY, $worldmapZ) = \explode(',', $loc);

        $params = [
            'textDomain'     => 'module_worldmapen',
            'terrain'        => worldmapen_getTerrain($worldmapX, $worldmapY, $worldmapZ),
            'showLocation'   => $showloc,
            'enableTerrains' => get_module_setting('enableTerrains', 'worldmapen'),
            'colorUserLoc'   => get_module_setting('colorUserLoc', 'worldmapen'),
            'terrainDef'     => worldmapen_loadTerrainDefs(),
        ];

        return $env->render('@module/worldmapen/twig/legend.twig', $params);
    }
}
