<?php

function funddrive_getpercent()
{
    $fundDrive = \LotgdCache::getItem('mod_funddrive');

    //-- Use cache to save queries
    if (! $fundDrive || is_array($fundDrive) || empty($fundDrive))
    {
        $targetmonth = get_module_setting('targetmonth', 'funddrive');
        $targetmonth = $targetmonth ?: date('m');

        $start = date('Y').'-'.$targetmonth.'-01';
        $end = date('Y-m-d', strtotime('+1 month', strtotime($start)));

        $repository = \Doctrine::getRepository('LotgdCore:Paylog');
        $query = $repository->createQueryBuilder('u');

        $row = $query->select('sum(u.amount) AS gross', 'sum(u.txfee) AS fees')
            ->where('u.processdate >= :start AND u. processdate < :end')

            ->setParameter('start', $start)
            ->setParameter('end', $end)

            ->getQuery()
            ->getSingleResult()
        ;

        $goal = (int) get_module_setting('goalamount', 'funddrive');
        $base = (int) get_module_setting('baseamount', 'funddrive');

        $current = $row['gross'] + $base;

        if (get_module_setting('deductfees', 'funddrive'))
        {
            $current -= $row['fees'];
        }

        $pct = round(($current / $goal) * 100, 0);


        $fundDrive = [
            'percent' => $pct,
            'goal' => $goal,
            'current' => $current
        ];

        \LotgdCache::setItem('mod_funddrive', $fundDrive);
    }

    return $fundDrive;
}
