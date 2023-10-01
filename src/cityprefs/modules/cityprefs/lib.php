<?php

function get_cityprefs_cityid(string $loc): int
{
    $cities = LotgdKernel::get('cache.app')->get('cityprefs-ids', function (\Symfony\Contracts\Cache\ItemInterface $item)
    {
        $item->expiresAt(new DateTime('tomorrow'));

        $repository = Doctrine::getRepository('LotgdLocal:ModuleCityprefs');
        $result = $repository->findAll();

        $cities = [];

        foreach ($result as $value)
        {
            $cities[$value->getCityName()] = $value->getId();
        }

        return $cities;
    });

    return $cities[$loc] ?? 0;
}
