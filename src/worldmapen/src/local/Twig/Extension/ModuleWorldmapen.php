<?php

namespace Lotgd\Local\Twig\Extension;

use Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen\Campers;
use Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen\Compass;
use Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen\Legend;
use Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen\Map;
use Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen\MapEditor;
use Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen\MapEditTerrain;
use Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen\MiniMap;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Lotgd\Core\Pvp\Listing;
use Lotgd\Core\Lib\Settings;

class ModuleWorldmapen extends AbstractExtension
{
    use Campers;
    use Compass;
    use Legend;
    use Map;
    use MapEditor;
    use MapEditTerrain;
    use MiniMap;

    private $pvp;
    private $settings;

    public function __construct(Listing $pvp, Settings $settings)
    {
        $this->pvp = $pvp;
        $this->settings = $settings;
    }

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
