<?php

namespace Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen;

trait Compass
{
    /**
     * World map key (Compass).
     */
    public function showCompass(array $params): string
    {
        $params['textDomain'] = 'module_worldmapen';

        return $this->getTemplate()->render('@module/worldmapen/twig/compass.twig', $params);
    }
}
