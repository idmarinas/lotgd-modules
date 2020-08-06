<?php

namespace Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen;

trait Map
{
    /**
     * Show full map.
     */
    public function showMap(array $params): string
    {
        if (! get_module_pref('worldmapbuy', 'worldmapen'))
        {
            return '';
        }

        $vloc = [];
        $vname = getsetting('villagename', LOCATION_FIELDS);
        $vloc[$vname] = 'village';
        $vloc = modulehook('validlocation', $vloc);

        $loc = get_module_pref('worldXYZ', 'worldmapen');
        list($worldmapX, $worldmapY, $worldmapZ) = explode(',', $loc);

        $cityMap = [];

        foreach ($vloc as $city => $val)
        {
            $cityX = get_module_setting($city.'X', 'worldmapen');
            $cityY = get_module_setting($city.'Y', 'worldmapen');

            $cityMap["{$cityX},{$cityY}"] = $city;
        }

        $params = [
            'textDomain' => 'module-worldmapen',
            'mapLinks' => $params,
            'colorUserLoc' => get_module_setting('colorUserLoc', 'worldmapen'),
            'sizeX' => get_module_setting('worldmapsizeX', 'worldmapen'),
            'sizeY' => get_module_setting('worldmapsizeY', 'worldmapen'),
            'showCompass' => (bool) get_module_setting('showcompass', 'worldmapen'),
            'worldMapX' => $worldmapX,
            'worldMapY' => $worldmapY,
            'worldMapZ' => $worldmapZ,
            'cityMap' => $cityMap,
            'worldMap' => worldmapen_loadMap(),
            'terrainColor' => worldmapen_getColorDefinitions()
        ];

        return $this->getTheme()->renderModuleTemplate('worldmapen/twig/map.twig', $params);
    }
}
