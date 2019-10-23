<?php

//-- Itemeffect restructured by IDMarinas 2016

//-- Your items can have this effects
//------------------------------------
//-- Alter charm -> itemeffects_alter_charm($points, $item)
//-- Alter deathpower -> itemeffects_alter_deathpower($points, $item)
//-- Alter donation points -> itemeffects_increased_donation($points, $item)
//-- Alter gems -> itemeffects_increased_gems($gems, $item)
//-- Alter gold -> itemeffects_increased_gold($gold, $item)
//-- Alter hitpoints -> itemeffects_restore_hitpoints($hitpoints, $item, $overrideMaxhitpoints = null, $canDie = true)
//-- Alter stamina -> itemeffects_restore_stamina($stamina, $item)
//-- Alter turns -> itemeffects_restore_turns($turns, $item)

require_once 'lib/itemeffects/charm.php';
require_once 'lib/itemeffects/deathpower.php';
require_once 'lib/itemeffects/donation.php';
require_once 'lib/itemeffects/gems.php';
require_once 'lib/itemeffects/gold.php';
require_once 'lib/itemeffects/health.php';
require_once 'lib/itemeffects/stamina.php';
require_once 'lib/itemeffects/turns.php';

/**
 * Get effect of item.
 *
 * @param false|array $item
 * @param string      $noeffecttext
 *
 * @return array Contain all messages
 */
function get_effect($item = false, $noeffecttext = '')
{
    global $session;

    $textDomain = 'module-inventory';

    $out = [];

    if (false === $item)
    {
        $effectText = $noeffecttext;
        if ('' == $noeffecttext)
        {
            $args = modulehook('item-noeffect', ['msg' => 'item.effect.text.nothing', 'params' => [], 'textDomain' => $textDomain, 'item' => $item]);
            $effectText = [ $args['msg'], $args['params'], $args['textDomain'] ];
        }

        $out[] = $effectText;
    }
    else
    {
        $noeffecttext = explode('|', $item['noEffectText'] ?: 'item.effect.text.nothing');
        $noeffecttext = $noeffecttext[0];
        $noeffecttextDomain = $noeffecttext[1] ?? $textDomain;

        if (inventory_can_use_item($item))
        {
            $execfile = "{$item['execValue']}.php";

            if (file_exists($execfile))
            {
                include_once $execfile;

                $args = modulehook('itemeffect', ['out' => $out, 'item' => $item]);
                $out = $args['out'];
            }
            else
            {
                $args = modulehook('item-noeffect', ['msg' => $noeffecttext, 'params' => [], 'textDomain' => $noeffecttextDomain, 'item' => $item]);
                $out[] = [ $args['msg'], $args['params'], $args['textDomain'] ];
                $out[] = [ 'item.effect.text.problem', [ 'itemName' => $item['name'], $textDomain]];

                debug("Cant load script of item {$item['itemid']}, file '{$execfile}' does not exists.");
            }
        }
        else
        {
            $args = modulehook('item-noeffect', ['msg' => $noeffecttext, 'params' => [], 'textDomain' => $noeffecttextDomain, 'item' => $item]);
            $out[] = [ $args['msg'], $args['params'], $args['textDomain'] ];
            $out[] = ['item.effect.text.requisites', [
                    'dragonkills' => $item['dragonkills'],
                    'level' => $item['level'],
                    'itemName' => $item['name']
                ],
                $textDomain
            ];
        }
    }

    if (0 == count($out))
    {
        $args = modulehook('item-noeffect', ['msg' => $noeffecttext, 'params' => [], 'textDomain' => $textDomain, 'item' => $item]);
        $out[] = [$args['msg'], $args['params'], $args['textDomain']];
    }

    if (! is_array($out))
    {
        $out = [$out];
    }

    return $out;
}

/**
 * Determinate if can use the item.
 *
 * @param array $item
 * @param bool  $details Returns details that say why cant use it
 *
 * @return bool|array
 */
function inventory_can_use_item($item, $details = false)
{
    global $session;

    $results = ['canUse' => true];
    //-- Not have DragonKills for use it
    if ($session['user']['dragonkills'] < $item['dragonkills'])
    {
        $results['dragonkills'] = 1;
        $results['dragonkillsText'] = 'Need %s DragonKills for use this item.';
        $results['canUse'] = false;
    }

    //-- Not have level for use it
    if ($session['user']['level'] < $item['level'])
    {
        $results['level'] = 1;
        $results['levelText'] = 'Need level %s for use this item.';
        $results['canUse'] = false;
    }

    //-- If pass first check, now check if item have a custom checker requisites for use
    if (isset($item['execRequisites']))
    {
        $requisitesFile = "{$item['execRequisites']}.php";

        if (file_exists($requisitesFile))
        {
            $result = include_once $requisitesFile;
            $results = array_merge($results, $result);
        }
    }

    if ($details)
    {
        return $results;
    }

    return $results['canUse'];
}
