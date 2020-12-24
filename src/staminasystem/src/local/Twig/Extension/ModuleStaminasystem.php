<?php

namespace Lotgd\Local\Twig\Extension;

use Lotgd\Core\Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleStaminasystem extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('module_staminasystem_get_player_action', [$this, 'moduleStaminasystemGetPlayerAction']),
            new TwigFunction('module_staminasystem_display_cost', [$this, 'moduleStaminasystemDisplayCost']),
        ];
    }

    /**
     * Get info of action.
     *
     * @param string $action
     * @param int    $user
     *
     * @return mixed
     */
    public function moduleStaminasystemGetPlayerAction($action, $user = false)
    {
        require_once 'modules/staminasystem/lib/lib.php';

        return get_player_action($action, $user);
    }

    /**
     * Get display cost.
     *
     * @param string $action
     * @param int    $precision
     * @param int    $user
     */
    public function moduleStaminasystemDisplayCost($action, $precision = 2, $user = false)
    {
        require_once 'modules/staminasystem/lib/lib.php';

        return stamina_getdisplaycost($action, $precision, $user);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'module-stamina-system';
    }
}
