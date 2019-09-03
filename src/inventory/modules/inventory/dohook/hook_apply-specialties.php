<?php

$skill = \LotgdHttp::getQuery('skill');

if ('ITEM' == $skill)
{
    require_once 'lib/buffs.php';

    $itemId = (int) \LotgdHttp::getQuery('l');
    $invId = (int) \LotgdHttp::getQuery('invid');

    $repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
    $item = $repository->extractEntity($repository->findBy([ 'id' =>$invId, 'item' => $itemId ]));
    $item['item'] = $repository->extractEntity($item['item']);

    if ($item['buff'] ?? false)
    {
        $item['buff'] = $repository->extractEntity($item['buff']);

        apply_buff($item['buff']['key'], array_merge([], ...array_map(
            function ($key, $value) { return [ strtolower($key) => $value ]; },
            array_keys($item['buff']),
            $item['buff']
        )));
    }

    if ($item['item']['execValue'] > '')
    {
        require_once 'lib/itemeffects.php';

        global $countround, $lotgdBattleContent;

        if ($item['item']['execText'] > '')
        {
            $text = explode('|', $item['item']['execText']);
            $lotgdBattleContent['battlerounds'][$countround]['allied'][] = [$text[0], [ 'itemName' => $item['item']['name'] ], $text[1] ?? $textDomain];
        }
        else
        {
            $lotgdBattleContent['battlerounds'][$countround]['allied'][] = ['item.activate', [ 'itemName' => $item['item']['name'] ], $textDomain];
        }

        $result = get_effect($item);

        foreach ($result as $key => $message)
        {
            $lotgdBattleContent['battlerounds'][$countround]['allied'][] = $message;
        }
    }

    if ($item['charges'] > 1)
    {
        uncharge_item($itemId, false, $invId);
    }
    elseif (isset($item['id']))//-- Have inventory ID
    {
        remove_item($itemId, 1, false, $invId);
    }
    else
    {
        remove_item($itemId, 1);
    }
}
