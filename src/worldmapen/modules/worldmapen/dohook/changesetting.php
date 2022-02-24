<?php

// We only care about the names of locations.
if ('villagename' == $args['setting'])
{
    $old = $args['old'];
    $new = $args['new'];
    // Handle any locations of the old name and convert them.
    $x = get_module_setting($old.'X');
    $y = get_module_setting($old.'Y');
    $z = get_module_setting($old.'Z');
    set_module_setting('worldmapen'.$new.'X', $x);
    set_module_setting('worldmapen'.$new.'Y', $y);
    set_module_setting('worldmapen'.$new.'Z', $z);
    set_module_setting('worldmapen'.$old.'X', '');
    set_module_setting('worldmapen'.$old.'Y', '');
    set_module_setting('worldmapen'.$old.'Z', '');
    // Handle any players who last city was the old name.
    $userPrefsRepository = Doctrine::getRepository('LotgdCore:ModuleUserprefs');
    $query               = $userPrefsRepository->getQueryBuilder();
    $query->update('LotgdCore:ModuleUserprefs', 'u')
        ->set('u.value', ':new')
        ->where('u.modulename = :module AND settings = :setting AND u.value = :old')

        ->setParameter('old', $old)
        ->setParameter('new', $new)
        ->setParameter('module', 'worldmapen')
        ->setParameter('setting', 'lastCity')

        ->getQuery()
        ->execute()
    ;
}
