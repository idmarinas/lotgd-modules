<?php

namespace Lotgd\Ajax\Local;

use Jaxon\Response\Response;
use Lotgd\Core\AjaxAbstract;

class ModCities extends AjaxAbstract
{
    public function faq(): Response
    {
        global $session;

        $response = new Response();

        $newbieisland = get_module_setting('villagename', 'newbieisland');

        $params = [
            'travels'          => get_module_setting('allowance', 'cities'),
            'capital'          => getsetting('villagename', LOCATION_FIELDS),
            'lodge'            => \file_exists('public/lodge.php'),
            'newbieIsland'     => is_module_active('newbieisland'),
            'newbieIslandName' => $newbieisland,
            'location'         => (isset($session['user']['location']) && $session['user']['location'] == $newbieisland),
        ];

        $title = \LotgdTranslator::t('section.faq.title', [], 'cities-module');

        $content = \LotgdTheme::render('@module/cities/run/faq.twig', $params);

        // Show the dialog
        $response->dialog->show($title, ['content' => $content], []);

        return $response;
    }
}
