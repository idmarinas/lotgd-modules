<?php

if ('World' == $args['loc'])
{
    $args['handled'] = 1;

    \LotgdResponse::pageAddContent(\LotgdTranslation::t('pvp.count', ['count' => $args['count']], 'module_worldmapen'));
}
