<?php

// translator ready
// mail ready
// addnews ready
require_once 'lib/buffs.php';

/**
 * Version History:
 * 1.1.0 (Idmarinas) Change to make party repeat each time add extra buff for cake and drink
 * 1.0.0 Original.
 */
function gardenparty_getmoduleinfo()
{
    return [
        'name'     => 'Garden Party',
        'author'   => 'Eric Stevens, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Gardens',
        'version'  => '3.0.0',
        'download' => 'core_module',
        'settings' => [
            'Garden Party Settings,title',
            'Note: party duration is 24 hours always,note',
            'partystart'  => 'When does the part start|2015-01-20 00:00:00',
            'partyrepeat' => 'How long does the party repeat|P1Y',
            'Note: http://php.net/manual/es/dateinterval.construct.php,note',
            'cakecost'   => 'Cost per level for cake,int|20',
            'maxcake'    => 'How many slices of cake can a player buy in one day?,int|3',
            'drinkcost'  => 'Cost per level for drink,int|50',
            'drinkemote' => 'What will display in the conversation when you order drink?|takes a big swig of Grape Soda.',
            'maxdrink'   => 'How many party drinks can a player buy in one day?,int|3',
        ],
        'prefs' => [
            'Garden Party User Preferences,title',
            'caketoday'   => 'How many pieces of cake have they eaten today?,int|0',
            'drinkstoday' => 'How many drinks have they had today in the partY?,int|0',
        ],
        'requires' => [
            'lotgd' => '>=5.5.0|Need a version equal or greater than 5.5.0 IDMarinas Edition',
        ],
    ];
}

function gardenparty_install()
{
    module_addhook('page-gardens-tpl-params');
    module_addhook('newday');

    return true;
}

function gardenparty_uninstall()
{
    \LotgdResponse::pageDebug('Uninstalling module.');

    return true;
}

/**
 * Check that is party day.
 */
function check_party_running()
{
    $interval = new DateInterval(get_module_setting('partyrepeat'));
    $start    = new DateTime(get_module_setting('partystart'));
    $end      = new DateTime('now');

    $period = new DatePeriod($start, $interval, $end);

    $periodArray = \iterator_to_array($period);
    $lastPeriod  = \end($periodArray);

    if (\strtotime($lastPeriod->format('Y-m-d')) == \strtotime($end->format('Y-m-d')))
    {
        return true;
    }

    return false;
}

function gardenparty_dohook($hookname, $args)
{
    global $session;

    $textDomain = 'module_gardenparty';

    \LotgdNavigation::setTextDomain($textDomain);

    switch ($hookname)
    {
        case 'newday':
            set_module_pref('caketoday', 0);
            set_module_pref('drinkstoday', 0);
        break;
        case 'page-gardens-tpl-params':
            // See if the party is currently running.
            if (check_party_running())
            {
                break;
            }

            $params = [
                'textDomain' => $textDomain,
                'barman'     => getsetting('barkeep', '`tCedrik`0'),
            ];

            $args['includeTemplatesPost']['@module/gardenparty/hook/gardens.twig'] = $params;

            \LotgdNavigation::addHeader('navigation.category.party');
            $caketoday   = get_module_pref('caketoday');
            $drinkstoday = get_module_pref('drinkstoday');
            $cakecost    = get_module_setting('cakecost')  * $session['user']['level'];
            $drinkcost   = get_module_setting('drinkcost') * $session['user']['level'];

            if ($caketoday < get_module_setting('maxcake') && $session['user']['gold'] >= $cakecost)
            {
                $cake = \LotgdTranslator::t('consumption.cake', [], $textDomain);
                \LotgdNavigation::addNav('navigation.nav.consumption', 'runmodule.php?module=gardenparty&buy=cake', [
                    'params' => ['name' => $cake, 'cost' => $cakecost],
                ]);
            }

            if ($drinkstoday < get_module_setting('maxdrink') && $session['user']['gold'] >= $drinkcost)
            {
                $drink = \LotgdTranslator::t('consumption.drink', [], $textDomain);
                \LotgdNavigation::addNav('navigation.nav.consumption', 'runmodule.php?module=gardenparty&buy=drink', [
                    'params' => ['name' => $drink, 'cost' => $drinkcost],
                ]);
            }
        break;
        default: break;
    }

    \LotgdNavigation::setTextDomain();

    return $args;
}

function gardenparty_run()
{
    global $session;

    // See if the party is currently running.
    if ( ! check_party_running())
    {
        return redirect('gardens.php');
    }

    $buy        = \LotgdRequest::getQuery('buy');
    $textDomain = 'module_gardenparty';
    $missed     = \LotgdTranslator::t('party.miss.item', [], $textDomain);
    $comment    = \LotgdTranslator::t('party.miss.comment', [], $textDomain);
    $cantafford = false;

    switch ($buy)
    {
        case 'cake':
            $caketoday = get_module_pref('caketoday');
            $cost      = get_module_setting('cakecost') * $session['user']['level'];

            $cake = \LotgdTranslator::t('consumption.cake', [], $textDomain);

            if ($session['user']['gold'] >= $cost)
            {
                $session['user']['gold'] -= $cost;
                $comment = \LotgdTranslator::t('consumption.mote', [], $textDomain);
                $msg     = \LotgdTranslator::t('buff.msg.cake', ['name' => $cake], $textDomain);
                $buff    = [
                    'name'     => $cake,
                    'defmod'   => 1.05,
                    'roundmsg' => $msg,
                    'rounds'   => 20,
                    'schema'   => 'module_gardenparty',
                ];
                LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('gardenparty-cake', $buff);
                set_module_pref('caketoday', $caketoday + 1);
            }
            else
            {
                //they probably timed out, and got PK'd.
                //Let's handle it gracefully.
                $cantafford = true;
                $missed     = $cake;
            }
        break;
        case 'drink':
            $cost        = get_module_setting('drinkcost') * $session['user']['level'];
            $drinkstoday = get_module_pref('drinkstoday');

            $drink = \LotgdTranslator::t('consumption.drink', [], $textDomain);

            if ($session['user']['gold'] >= $cost)
            {
                $session['user']['gold'] -= $cost;
                $msg  = \LotgdTranslator::t('buff.msg.drink', ['name' => $drink], $textDomain);
                $buff = [
                    'name'     => $drink,
                    'atkmod'   => 1.05,
                    'roundmsg' => $msg,
                    'rounds'   => 20,
                    'schema'   => 'module_gardenparty',
                ];
                LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('gardenparty-drink', $buff);
                set_module_pref('drinkstoday', $drinkstoday + 1);
            }
            else
            {
                //they probably timed out, and got PK'd.
                //Let's handle it gracefully.
                $cantafford = true;
                $missed     = $drink;
            }
        break;
        default: break;
    }

    if ($cantafford)
    {
        $settings = LotgdKernel::get('lotgd_core.settings');

        \LotgdResponse::pageStart('title', [
            'barman'  => $settings->getSetting('barkeep', '`tCedrik`0'),
            'clothes' => \LotgdTranslator::t('section.hook.gardens.party.barman.clothes', [], $textDomain),
        ], $textDomain);

        \LotgdNavigation::addNav('navigation.nav.return', 'gardens.php');

        $params = [
            'textDomain' => $textDomain,
            'missed'     => $missed,
            'barman'     => $settings->getSetting('barkeep', '`tCedrik`0'),
            'partyType'  => \LotgdTranslator::t('party.type', [], $textDomain),
        ];

        \LotgdResponse::pageAddContent(\LotgdTheme::render('@module/gardenparty/run.twig', $params));

        \LotgdResponse::pageEnd();
    }
    else
    {
        \LotgdKernel::get(Lotgd\Core\Output\Commentary::class)->saveComment([
            'section' => 'gardens',
            'comment' => ': '.$comment
        ]);
        $buff = [
            'name'            => \LotgdTranslator::t('buff.name.miss', [], $textDomain),
            'minioncount'     => 1,
            'maxbadguydamage' => 0,
            'minbadguydamage' => 0,
            'effectnodmgmsg'  => \LotgdTranslator::t('buff.msg.miss', [], $textDomain),
            'rounds'          => -1,
            'schema'          => 'module_gardenparty',
        ];
        LotgdKernel::get('lotgd_core.combat.buffer')->applyBuff('gardenparty', $buff);

        return redirect('gardens.php');
    }
}
