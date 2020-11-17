<?php

namespace Lotgd\Ajax\Local;

use Jaxon\Response\Response;
use Lotgd\Core\AjaxAbstract;

class ModCreationAddon extends AjaxAbstract
{
    public function privacy(): Response
    {
        $response = new Response();

        $params = [
            'privacy' => get_module_setting('privacy', 'creationaddon')
        ];
        $content = \LotgdTheme::render('@module/creationaddon/run/privacy.twig', $params);

        // Show the dialog
        $response->dialog->show('', ['content' => $content ?: '---'], []);

        return $response;
    }

    public function terms(): Response
    {
        $response = new Response();

        $params = [
            'terms' => get_module_setting('terms', 'creationaddon')
        ];

        $content = \LotgdTheme::render('@module/creationaddon/run/terms.twig', $params);

        // Show the dialog
        $response->dialog->show('', ['content' => $content ?: '---'], []);

        return $response;
    }
}
