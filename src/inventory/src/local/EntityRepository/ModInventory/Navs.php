<?php

namespace Lotgd\Local\EntityRepository\ModInventory;

use Tracy\Debugger;

/**
 * Functions for navs.
 */
trait Navs
{
    /**
     * Get items for a nav.
     */
    public function getItemsForNav(int $constant, int $acctId): array
    {
        $query = $this->createQueryBuilder('u');

        try
        {
            $result = $query->select('u', 'count(u.item) AS quantity', 'sum(u.charges) AS charges')
                ->leftJoin('LotgdLocal:ModInventoryItem', 'i', 'with', $query->expr()->eq('i.id', 'u.item'))

                ->where('BIT_AND(i.activationHook, :constant) > 0 AND u.userId = :user')

                ->groupBy('u.item')

                ->orderBy('i.class', 'ASC')
                ->addOrderBy('i.name', 'ASC')

                ->setParameter('user', $acctId)
                ->setParameter('constant', $constant)

                ->getQuery()
                ->getResult()
            ;

            $inventory = [];

            foreach ($result as $key => $item)
            {
                $inventory[$key] = \array_merge($this->extractEntity($item[0]), $item);
                unset($inventory[$key][0]);
            }

            return $inventory;
        }
        catch (\Throwable $th)
        {
            Debugger::log($th);

            return [];
        }
    }
}
