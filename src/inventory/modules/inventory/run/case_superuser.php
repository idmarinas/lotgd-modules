<?php

$op2 = (string) \LotgdHttp::getQuery('op2');
$acctId = (int) \LotgdHttp::getQuery('acctid');
$return = (string) \LotgdHttp::getQuery('return');
$creturn = urlencode($return);

$repository = \Doctrine::getRepository('LotgdLocal:ModInventory');
$accountRep = \Doctrine::getRepository('LotgdCore:Accounts');

$name = $accountRep->getCharacterNameFromAcctId($acctId);

page_header('title.superuser', ['name' => \LotgdSanitize::fullSanitize($name)], $textDomain);

if ('dropitem' == $op2)
{
    $id = (int) \LotgdHttp::getQuery('id');
    $invId = (int) \LotgdHttp::getQuery('invid');

    remove_item($id, 1, $acctId, $invId);
}

\LotgdNavigation::addHeader('common.category.navigation', ['textDomain' => 'navigation-app']);
\LotgdNavigation::superuserGrottoNav();
\LotgdNavigation::addNav('navigation.nav.return.whence', $return, ['textDomain' => $textDomain]);
\LotgdNavigation::addNav('navigation.nav.update', "runmodule.php?module=inventory&op=superuser&acctid={$acctId}&return={$creturn}", ['textDomain' => $textDomain]);

$params = [
    'textDomain' => $textDomain,
    'inventory' => $repository->getInventoryOfCharacter($acctId),
    'limitTotal' => get_module_setting('limit', 'inventory'),
    'weightTotal' => get_module_setting('weight', 'inventory'),
    'owner' => $name,
    'ownerId' => $acctId
];

rawoutput(\LotgdTheme::renderModuleTemplate('inventory/run/superuser.twig', $params));

page_footer();
