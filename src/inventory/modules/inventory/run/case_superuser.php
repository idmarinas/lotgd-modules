<?php

$op2     = (string) \LotgdRequest::getQuery('op2');
$acctId  = (int) \LotgdRequest::getQuery('acctid');
$return  = (string) \LotgdRequest::getQuery('return');
$creturn = \urlencode($return);

$repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
$accountRep = \Doctrine::getRepository('LotgdCore:Accounts');

$name = $accountRep->getCharacterNameFromAcctId($acctId);

\LotgdResponse::pageStart('title.superuser', ['name' => \LotgdSanitize::fullSanitize($name)], $textDomain);

if ('dropitem' == $op2)
{
    $id    = (int) \LotgdRequest::getQuery('id');
    $invId = (int) \LotgdRequest::getQuery('invid');

    remove_item($id, 1, $acctId, $invId);
}

\LotgdNavigation::addHeader('common.category.navigation', ['textDomain' => 'navigation-app']);
\LotgdNavigation::superuserGrottoNav();
\LotgdNavigation::addNav('navigation.nav.return.whence', $return, ['textDomain' => $textDomain]);
\LotgdNavigation::addNav('navigation.nav.update', "runmodule.php?module=inventory&op=superuser&acctid={$acctId}&return={$creturn}", ['textDomain' => $textDomain]);

$params = [
    'textDomain'  => $textDomain,
    'inventory'   => $repository->getInventoryOfCharacter($acctId),
    'limitTotal'  => get_module_setting('limit', 'inventory'),
    'weightTotal' => get_module_setting('weight', 'inventory'),
    'owner'       => $name,
    'ownerId'     => $acctId,
];

\LotgdResponse::pageAddContent(\LotgdTheme::renderModuleTemplate('inventory/run/superuser.twig', $params));

\LotgdResponse::pageEnd();
