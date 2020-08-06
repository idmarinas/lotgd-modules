<?php

require_once 'lib/buffs.php';

global $session;

$id = (int) \LotgdHttp::getQuery('id');
$invId = (int) \LotgdHttp::getQuery('invid');
$return = (string) \LotgdHttp::getQuery('return');
$return = \LotgdSanitize::cmdSanitize($return);

$repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
$item = $repository->getItemOfInventoryOfCharacter($id, $session['user']['acctid']);

if (false !== strpos($return, 'forest.php'))
{
    $return = 'forest.php';
}

if (false !== strpos($return, 'village.php'))
{
    $return = 'village.php';
}

if ($item['charges'] > 1)
{
    uncharge_item($id, false, $invId);
}
elseif (isset($invId))
{
    remove_item($id, 1, false, $invId);
}
else
{
    remove_item($id, 1);
}

if (($item['item']['buff'] ?? false) && ! empty($item['item']['buff']))
{
    apply_buff($item['item']['buff']['key'], array_merge([], ...array_map(
        function ($key, $value) { return [strtolower($key) => $value]; },
        array_keys($item['item']['buff']),
        $item['item']['buff']
    )));
}

if ($item['item']['execvalue'] > '')
{
    page_header($item['item']['name']);

    $params = [];

    if ($item['item']['exectext'] > '')
    {
        $text = explode('|', $item['item']['exectext']);
        $params['activate'] = [
            $text[0],
            [
                'itemName' => $item['item']['name']
            ],
            $text[1] ?? $textDomain
        ];
    }
    else
    {
        $params['activate'] = [
            'item.activate',
            [
                'itemName' => $item['item']['name']
            ],
            $textDomain
        ];
    }

    require_once 'lib/itemeffects.php';

    $params['messages'] = get_effect($item['item'], $item['item']['noeffecttext']);

    \LotgdNavigation::addHeader('navigation.category.return');

    if ($session['user']['hitpoints'] <= 0 || ! $session['user']['alive'])
    {
        \LotgdNavigation::addNav('navigation.nav.news', 'news.php');
    }
    else
    {
        display_item_nav(LotgdHttp::getQuery('hookname'), $return);
        \LotgdNavigation::addNav('navigation.nav.return.whence', $return);
    }

    rawoutput(\LotgdTheme::renderModuleTemplate('inventory/run/activate.twig', $params));

    page_footer();
}

return redirect($return);
