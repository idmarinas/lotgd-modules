<?php

namespace Lotgd\Local\Controller;

use Lotgd\Core\Controller\LotgdControllerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Lotgd\Core\Lib\Settings;

class CitiesController extends AbstractController implements LotgdControllerInterface
{
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function faq(): Response
    {
        global $session;

        $newbieisland = get_module_setting('villagename', 'newbieisland');

        $params = [
            'translation_domain' => 'cities_module',
            'travels'          => get_module_setting('allowance', 'cities'),
            'capital'          => $this->settings->getSetting('villagename', LOCATION_FIELDS),
            'lodge'            => \file_exists('public/lodge.php'),
            'newbieIsland'     => is_module_active('newbieisland'),
            'newbieIslandName' => $newbieisland,
            'location'         => (isset($session['user']['location']) && $session['user']['location'] == $newbieisland),
        ];

        return $this->render('@module/cities/run/faq.twig', $params);
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
