<?php

namespace Lotgd\Local\Twig\Extension;

use Lotgd\Core\Pattern as PatternCore;
use Lotgd\Core\Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleWorldmapen extends AbstractExtension
{
    use PatternCore\Theme;
    use PatternCore\Translator;
    use Pattern\Campers;
    use Pattern\Compass;
    use Pattern\Legend;
    use Pattern\MapEditor;
    use Pattern\MapEditTerrain;
    use Pattern\Map;
    use Pattern\MiniMap;

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('module_worldmapen_show_map', [$this, 'showMap']),
            new TwigFunction('module_worldmapen_show_mini_map', [$this, 'showMiniMap']),
            new TwigFunction('module_worldmapen_show_compass', [$this, 'showCompass']),
            new TwigFunction('module_worldmapen_show_legend', [$this, 'showLegend']),
            new TwigFunction('module_worldmapen_show_campers', [$this, 'showCampers']),

            new TwigFunction('module_worldmapen_show_map_editor', [$this, 'showMapEditor']),
            new TwigFunction('module_worldmapen_show_map_edit_terrain', [$this, 'showMapEditTerrain']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'module-worldmapen';
    }
}
