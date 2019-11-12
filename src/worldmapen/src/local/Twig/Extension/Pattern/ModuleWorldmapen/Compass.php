<?php

namespace Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen;

trait Compass
{
    /**
     * World map key (Compass).
     *
     * @param array $params
     *
     * @return string
     */
    public function showCompass(array $params): string
    {
        $params['textDomain'] = 'module-worldmapen';

        return $this->getTheme()->renderModuleTemplate('worldmapen/twig/compass.twig', $params);
    }
}
