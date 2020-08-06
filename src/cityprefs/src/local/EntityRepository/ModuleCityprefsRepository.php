<?php

namespace Lotgd\Local\EntityRepository;

use Lotgd\Core\Doctrine\ORM\EntityRepository as DoctrineRepository;
use Tracy\Debugger;

class ModuleCityprefsRepository extends DoctrineRepository
{
    /**
     * Get city name from city ID.
     */
    public function getCityNameById(int $cityId): ?string
    {
        try
        {
            $query = $this->createQueryBuilder('u');

            return $query->select('u.cityName')
                ->where('u.id = :id')

                ->setParameter('id', $cityId)

                ->getQuery()
                ->getSingleScalarResult()
            ;
        }
        catch (\Throwable $th)
        {
            Debugger::log($th);

            return null;
        }
    }

    /**
     * Get module name from city ID.
     */
    public function getModuleNameByCityId(int $cityId): ?string
    {
        try
        {
            $query = $this->createQueryBuilder('u');

            return $query->select('u.module')
                ->where('u.id = :id')

                ->setParameter('id', $cityId)

                ->getQuery()
                ->getSingleScalarResult()
            ;
        }
        catch (\Throwable $th)
        {
            Debugger::log($th);

            return null;
        }
    }
}
