<?php

page_header('title.superuser', [], $textDomain);

$actions = get_default_action_list();

\LotgdNavigation::superuserGrottoNav();

$params = [
    'textDomain' => $textDomain,
    'actions' => $actions
];

rawoutput(LotgdTheme::renderModuleTemplate('staminasystem/run/superuser.twig', $params));

page_footer();
