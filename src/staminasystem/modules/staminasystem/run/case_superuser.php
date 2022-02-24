<?php

LotgdResponse::pageStart('title.superuser', [], $textDomain);

$actions = get_default_action_list();

LotgdNavigation::superuserGrottoNav();

$params = [
    'textDomain' => $textDomain,
    'actions'    => $actions,
];

LotgdResponse::pageAddContent(LotgdTheme::render('@module/staminasystem/run/superuser.twig', $params));

LotgdResponse::pageEnd();
