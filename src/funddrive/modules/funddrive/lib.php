<?php

function funddrive_getpercent()
{
    $fundDrive = datacache('mod_funddrive', 86400);

    //-- Use cache to save queries
    if (! $fundDrive || is_array($fundDrive) || empty($fundDrive))
    {
        $targetmonth = get_module_setting('targetmonth');
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

        $goal = get_module_setting('goalamount');
        $base = get_module_setting('baseamount');

        $current = $row['gross'] + $base;

        if (get_module_setting('deductfees'))
        {
            $current -= $row['fees'];
        }

        $pct = round($current / $goal * 100, 0);


        $fundDrive = [
            'percent' => $pct,
            'goal' => $goal,
            'current' => $current
        ];

        updatedatacache('mod_funddrive', $fundDrive, true);
    }

    return $fundDrive;
}
