<?php

namespace Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen;

use Twig\Environment;

trait Compass
{
    /**
     * World map key (Compass).
     */
    public function showCompass(Environment $env, array $params): string
    {
        $params['textDomain'] = 'module_worldmapen';

        return $env->render('@module/worldmapen/twig/compass.twig', $params);
    }
}
