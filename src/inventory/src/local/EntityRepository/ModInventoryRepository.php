<?php

namespace Lotgd\Local\EntityRepository;

use Lotgd\Local\EntityRepository\ModInventory\Backup;
use Lotgd\Local\EntityRepository\ModInventory\Navs;
use Throwable;
use Lotgd\Core\Doctrine\ORM\EntityRepository as DoctrineRepository;
use Tracy\Debugger;

class ModInventoryRepository extends DoctrineRepository
{
    use Backup;
    use Navs;

    /**
     * Get item list of inventory for character.
     */
    public function getInventoryOfCharacter(int $acctId): array
    {
        $query = $this->createQueryBuilder('u');
        $expr  = $query->expr();

        try
        {
            $inventory = [];
            //-- Equip items
            $result = $query->select('u')
                ->leftJoin('LotgdLocal:ModInventoryItem', 'i', 'with', $expr->eq('i.id', 'u.item'))

                ->where('u.userId = :user AND i.hide = 0 AND i.equippable = 1')

                ->orderBy('i.class', 'ASC')
                ->addOrderBy('i.name', 'ASC')

                ->setParameter('user', $acctId)

                ->getQuery()
                ->getResult()
            ;

            foreach ($result as $item)
            {
                $data             = $this->extractEntity($item);
                $data['quantity'] = 1;
                $data['item']     = $this->extractEntity($data['item']);
                $inventory[]      = $data;
            }

            $queryNoEquip = clone $query;
            //-- No equip items
            $result = $queryNoEquip->select('u', 'count(u.item) AS quantity', 'sum(u.charges) AS charges')
                ->where('u.userId = :user AND i.hide = 0 AND i.equippable = 0')

                ->groupBy('u.item')

                ->getQuery()
                ->getResult()
            ;

            foreach ($result as $item)
            {
                $data         = \array_merge($this->extractEntity($item[0]), $item);
                $data['item'] = $this->extractEntity($data['item']);
                unset($data[0]);
                $inventory[] = $data;
            }

            return $inventory;
        }
        catch (Throwable $th)
        {
            Debugger::log($th);

            return [];
        }
    }

    /**
     * Get a full info of item in inventory.
     */
    public function getItemOfInventoryOfCharacter(int $itemId, int $acctId, int $invid = 0): array
    {
        $query = $this->createQueryBuilder('u');
        $query->expr();

        try
        {
            $query->select('u', 'count(u.item) AS quantity', 'sum(u.charges) AS charges')

                ->where('u.userId = :user AND u.item = :item')

                ->setParameter('user', $acctId)
                ->setParameter('item', $itemId)

                ->setMaxResults(1)
            ;

            if ($invid !== 0)
            {
                $query->andWhere('u.id = :inv')
                    ->setParameter('inv', $invid)
                ;
            }

            $result = $query->getQuery()->getSingleResult();

            $data = [];

            if ($result[0] ?? false)
            {
                $data                 = \array_merge($this->extractEntity($result[0]), $result);
                $data['item']         = $this->extractEntity($data['item']);
                $data['item']['buff'] = $this->extractEntity($data['item']['buff']);
                unset($data[0]);
            }

            return $data;
        }
        catch (Throwable $th)
        {
            Debugger::log($th);

            return [];
        }
    }
}
