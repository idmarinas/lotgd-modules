<?php

namespace Lotgd\Local\Twig\Extension\Pattern\ModuleWorldmapen;

use Twig\Environment;

trait Campers
{
    /**
     * World Map camping routine.
     */
    public function showCampers(Environment $env): string
    {
        global $session;

        if ( ! getsetting('pvp', 1))
        {
            return '';
        }

        $query = $this->pvp->getQuery();

        $loc = get_module_pref('worldXYZ', 'worldmapen');

        $query
            ->leftJoin('LotgdCore:ModuleUserprefs', 'mup', 'with', $query->expr()->eq('mup.userid', 'a.acctid'))

            ->andWhere('mup.value = :maploc')

            ->setParameter('maploc', $loc)
        ;

        $this->pvp->setQuery($query);

        $pvptime = getsetting('pvptimeout', 600);

        $params['paginator']  = $this->pvp->getPvpList($session['user']['location']);
        $params['sleepers']   = $this->pvp->getLocationSleepersCount($session['user']['location']);
        $params['returnLink'] = \preg_replace('/op=[a-z]*/', 'op=continue', \LotgdRequest::getServer('REQUEST_URI'));
        $params['pvpTimeOut'] = new \DateTime(\date('Y-m-d H:i:s', \strtotime("-{$pvptime} seconds")));

        $params['linkBase']  = 'runmodule.php?module=worldmapen';
        $params['linkExtra'] = '&op=combat&pvp=1';

        return $env->render('@module/worldmapen/twig/campers.twig', $params);
    }
}
