<?php

if (
    'LotgdLocal:ModInventory' == $args['entity'] //-- Hook return this format as base if you use this format
    || 'LotgdLocal_ModInventory' == $args['entity']
    || 'Lotgd\Local\ModInventory' == trim($args['entity']) //-- Hook return this format as base if you use this format
) {
    try
    {
        $repositoryItem = \Doctrine::getRepository('LotgdLocal:ModInventoryItem');
        $repository = \Doctrine::getRepository($args['data']['entity']);

        foreach ($args['data']['rows'] as $row)
        {
            $row = (array) $row;

            if ($row['item'])
            {
                $row['item'] = $repositoryItem->find($row['item']);

                //-- If not found item, not restore
                if (! $row['item'])
                {
                    continue;
                }
            }

            \Doctrine::persist($repository->hydrateEntity($row));
        }

        \Doctrine::flush();

        $args['proccessed'] = true;
    }
    catch (\Throwable $th)
    {
        \Tracy\Debugger::log($th);
    }
}
