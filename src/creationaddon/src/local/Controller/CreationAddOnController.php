<?php

namespace Lotgd\Local\Controller;

use Lotgd\Core\Controller\LotgdControllerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CreationAddOnController extends AbstractController implements LotgdControllerInterface
{
    public function privacy(): Response
    {
        $params = [
            'privacy' => get_module_setting('privacy', 'creationaddon'),
        ];

        return $this->render('@module/creationaddon/run/privacy.twig', $params);
    }

    public function terms(): Response
    {
        $params = [
            'terms' => get_module_setting('terms', 'creationaddon'),
        ];

        return $this->render('@module/creationaddon/run/terms.twig', $params);
    }


    /**
     * @inheritDoc
     */
    public function allowAnonymous(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function overrideForcedNav(): bool
    {
        return true;
    }
}
