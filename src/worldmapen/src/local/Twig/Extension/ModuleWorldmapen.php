<?php

namespace Lotgd\Local\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleWorldmapen extends AbstractExtension
{
    use Pattern\ModuleWorldmapen\Campers;
    use Pattern\ModuleWorldmapen\Compass;
    use Pattern\ModuleWorldmapen\Legend;
    use Pattern\ModuleWorldmapen\Map;
    use Pattern\ModuleWorldmapen\MapEditor;
    use Pattern\ModuleWorldmapen\MapEditTerrain;
    use Pattern\ModuleWorldmapen\MiniMap;

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('module_worldmapen_show_map', [$this, 'showMap'], ['needs_environment' => true]),
            new TwigFunction('module_worldmapen_show_mini_map', [$this, 'showMiniMap'], ['needs_environment' => true]),
            new TwigFunction('module_worldmapen_show_compass', [$this, 'showCompass'], ['needs_environment' => true]),
            new TwigFunction('module_worldmapen_show_legend', [$this, 'showLegend'], ['needs_environment' => true]),
            new TwigFunction('module_worldmapen_show_campers', [$this, 'showCampers'], ['needs_environment' => true]),

            new TwigFunction('module_worldmapen_show_map_editor', [$this, 'showMapEditor'], ['needs_environment' => true]),
            new TwigFunction('module_worldmapen_show_map_edit_terrain', [$this, 'showMapEditTerrain'], ['needs_environment' => true]),
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
