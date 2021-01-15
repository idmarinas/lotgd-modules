<?php

namespace Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen;

trait MiniMap
{
    /**
     * Show small map.
     */
    public function showMiniMap(array $params): string
    {
        $vloc         = [];
        $vname        = getsetting('villagename', LOCATION_FIELDS);
        $vloc[$vname] = 'village';
        $vloc         = modulehook('validlocation', $vloc);

        $viewRadius                              = get_module_setting('viewRadius', 'worldmapen');
        $loc                                     = get_module_pref('worldXYZ', 'worldmapen');
        list($worldmapX, $worldmapY, $worldmapZ) = \explode(',', $loc);

        $cityMap = [];

        foreach ($vloc as $city => $val)
        {
            $cityX = get_module_setting($city.'X', 'worldmapen');
            $cityY = get_module_setting($city.'Y', 'worldmapen');

            $cityMap["{$cityX},{$cityY}"] = $city;
        }

        $params = [
            'textDomain'   => 'module_worldmapen',
            'mapLinks'     => $params,
            'colorUserLoc' => get_module_setting('colorUserLoc', 'worldmapen'),
            'sizeX'        => get_module_setting('worldmapsizeX', 'worldmapen'),
            'sizeY'        => get_module_setting('worldmapsizeY', 'worldmapen'),
            'showCompass'  => (bool) get_module_setting('showcompass', 'worldmapen'),
            'smallMapSize' => (2 * $viewRadius) + 1,
            'worldMapX'    => $worldmapX,
            'worldMapY'    => $worldmapY,
            'worldMapZ'    => $worldmapZ,
            'cityMap'      => $cityMap,
            'worldMap'     => worldmapen_loadMap(),
            'terrainColor' => worldmapen_getColorDefinitions(),
        ];

        $params['rowSpanY'] = $params['sizeY'] + 1;

        return $this->getTemplate()->render('@module/worldmapen/twig/mini-map.twig', $params);
    }
}
