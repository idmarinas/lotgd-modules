<?php
/*
    World Map
    Originally by: Aes
    Updates & Maintenance by: Kevin Hatfield - Arune (khatfield@ecsportal.com)
    Updates & Maintenance by: Roland Lichti - klenkes (klenkes@paladins-inn.de)
    http://www.dragonprime.net
    Updated: Feb 23, 2008
 */

global $nlink, $elink, $wlink, $slink, $nelink, $nwlink, $selink, $swlink;
$nlink = $elink = $wlink = $slink = $nelink = $nwlink = $selink = $swlink = '#';

function worldmapen_editor_real()
{
    global $session;

    $textDomain = 'module_worldmapen';

    $params = [
        'textDomain' => $textDomain,
    ];

    \LotgdResponse::pageStart('title.editor', [], $params['textDomain']);

    \LotgdNavigation::superuserGrottoNav();

    // initialize the internal static maps
    worldmapen_loadMap();
    worldmapen_loadTerrainDefs();

    \LotgdNavigation::addHeader('navigation.category.editor');

    $op    = \LotgdRequest::getQuery('op');
    $act   = \LotgdRequest::getQuery('act');
    $subop = \LotgdRequest::getQuery('subop');

    \LotgdResponse::pageDebug("op={$op}, act={$act}, subop={$subop}");

    switch ($subop)
    {
        case 'regen': worldmapen_defaultcityloc(); break;
        case 'manual':
            $params['tpl'] = 'manual';

            \LotgdNavigation::addNav('navigation.nav.editor.return', 'runmodule.php?module=worldmapen&op=edit&admin=true');

            $vloc         = [];
            $vname        = getsetting('villagename', LOCATION_FIELDS);
            $vloc[$vname] = 'village';
            $vloc         = modulehook('validlocation', $vloc);
            \ksort($vloc);

            if (\LotgdRequest::isPost())
            {
                foreach ($vloc as $loc => $val)
                {
                    $space_valx = \preg_replace('/\s/', '_', $loc.'X');
                    $space_valy = \preg_replace('/\s/', '_', $loc.'Y');
                    set_module_setting($loc.'X', \LotgdRequest::getPost($space_valx));
                    set_module_setting($loc.'Y', \LotgdRequest::getPost($space_valy));
                    set_module_setting($loc.'Z', 1);
                }

                \LotgdFlashMessages::addSuccessMessage(\LotgdTranslator::t('flash.message.saved.settings', [], $textDomain));

                \reset($vloc);
            }

            $params['maxX'] = get_module_setting('worldmapsizeX');
            $params['maxY'] = get_module_setting('worldmapsizeY');

            $worldarray = ['World Locations,title'];

            foreach ($vloc as $loc => $val)
            {
                //Added to allow setting cities outside of the map. - Making cities inaccessible via normal travel.
                $myx                  = $params['maxX'] + 1;
                $worldarray[]         = "Locations for {$loc},note";
                $worldarray[$loc.'X'] = [" {$loc}: X Coordinate,range,1,{$myx},1"];
                $worldarray[$loc.'Y'] = [" {$loc}: Y coordinate,range,1,{$params['maxY']},1"];
            }

            require_once 'lib/showform.php';

            $row            = load_module_settings('worldmapen');
            $params['form'] = lotgd_showform($worldarray, $row, false, false, false);
        break;
        case 'terrain':
            $params['tpl'] = 'terrain';

            if (\LotgdRequest::isPost())
            {
                $map  = worldmapen_loadMap(null);
                $post = \LotgdRequest::getPostAll();

                \reset($post);

                foreach ($post as $key => $value)
                {
                    list($x, $y) = \explode('_', $key, 2);

                    if (\is_numeric($x) && \is_numeric($y))
                    {
                        $map = worldmapen_setTerrain($map, $x, $y, 1, $value);
                    }
                }

                worldmapen_saveMap($map);

                \LotgdFlashMessages::addSuccessMessage(\LotgdTranslator::t('flash.message.saved.map', [], $textDomain));
            }
        break;

        default: $params['tpl'] = 'default'; break;
    }

    \LotgdNavigation::addNav('navigation.nav.editor.regen', 'runmodule.php?module=worldmapen&op=edit&subop=regen');
    \LotgdNavigation::addNav('navigation.nav.editor.manual', 'runmodule.php?module=worldmapen&op=edit&subop=manual');
    \LotgdNavigation::addNav('navigation.nav.editor.terrain', 'runmodule.php?module=worldmapen&op=edit&subop=terrain');

    \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/worldmapen/editor.twig', $params));

    \LotgdResponse::pageEnd();
}
