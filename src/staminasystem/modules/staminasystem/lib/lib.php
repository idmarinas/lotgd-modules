<?php
/*
Altered core files to make the Stamina system work,based on 1.1.1:

battle.php
Added two hooks, one at the start of each round and one at the end

newday.php
Commented out portions of the code pertaining to spending DK points on forest fights
Commented out "Turns for today set to [whatever]"
*/

/*
=======================================================
GET DEFAULT ACTION LIST
Returns arrays for every Action default.
=======================================================
*/

function get_default_action_list()
{
    $actions = \unserialize(get_module_setting('actionsarray', 'staminasystem'));

    if ( ! \is_array($actions))
    {
        $actions = [];
        set_module_setting('actionsarray', \serialize($actions), 'staminasystem');
    }

    return $actions;
}

/*
=======================================================
GET PLAYER ACTION LIST
Returns arrays for every action for the given player.
=======================================================
*/

function get_player_action_list($userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $actions = \unserialize(get_module_pref('actions', 'staminasystem', $userid));

    if ( ! \is_array($actions))
    {
        $actions = [];
        set_module_pref('actions', \serialize($actions), 'staminasystem', $userid);
    }

    return $actions;
}

/*
=======================================================
GET ACTION DETAILS
Returns full info array for a given Action in a player's inventory.
Also sets default values if the player has not yet performed that action.
Returns False if the action is not installed.
=======================================================
*/

function get_player_action($action, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $playeractions = \unserialize(get_module_pref('actions', 'staminasystem', $userid));
    //Check to see if this action is set for this player, and if not, set it
    if ( ! isset($playeractions[$action]))
    {
        $defaultactions = get_default_action_list();

        if (isset($defaultactions[$action]))
        {
            $playeractions[$action]                    = $defaultactions[$action];
            $playeractions[$action]['lvl']             = 0;
            $playeractions[$action]['exp']             = 0;
            $playeractions[$action]['levelledup']      = false;
            $playeractions[$action]['naturalcost']     = $defaultactions[$action]['maxcost'];
            $playeractions[$action]['naturalcostbase'] = $defaultactions[$action]['maxcost'];
            set_module_pref('actions', \serialize($playeractions), 'staminasystem', $userid);

            return $playeractions[$action];
        }

        return false;
    }

    if ( ! stamina_check_action($action, $playeractions, $userid))
    {
        return get_player_action($action, $userid);
    }

    return $playeractions[$action];
}

/*
*******************************************************
INSTALL ACTION
Used in modules' Install fields, this sets the default values for this Action.
*******************************************************
*/

function install_action($actionname, $action)
{
    global $session;
    $defaultactions              = get_default_action_list();
    $defaultactions[$actionname] = $action;
    set_module_setting('actionsarray', \serialize($defaultactions), 'staminasystem');

    return true;
}

/*
*******************************************************
UNINSTALL ACTION
Cleans up all data pertaining to an action.  Use this in your module's Uninstall function.
*******************************************************
*/

function uninstall_action($actionname)
{
    //Remove information from the actions array
    $defaultactions = get_default_action_list();
    unset($defaultactions[$actionname]);
    set_module_setting('actionsarray', \serialize($defaultactions), 'staminasystem');
    //Now remove the action from each user's modulepref
    $query   = \Doctrine::createQueryBuilder();
    $results = $query->from('LotgdCore:User', 'u')
        ->select('u.acctid')

        ->getQuery()
        ->getResult()
    ;

    foreach ($results as $row)
    {
        $playeractions = \unserialize(get_module_pref('actions', 'staminasystem', $row['acctid']));
        unset($playeractions[$actionname]);
        set_module_pref('actions', \serialize($playeractions), 'staminasystem', $row['acctid']);
    }

    return true;
}

/*
*******************************************************
SET A BUFF
Temporarily increase or reduce the cost of and/or experience gained from performing an action.
*******************************************************
*/

function apply_stamina_buff($referencename, $buff, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    $bufflist                 = \unserialize(get_module_pref('buffs', 'staminasystem', $userid));
    $bufflist[$referencename] = $buff;
    set_module_pref('buffs', \serialize($bufflist), 'staminasystem', $userid);
}

/*
*******************************************************
CALCULATE ACTION COST
Returns the cost of performing an action, taking buffs into account.
*******************************************************
*/

function stamina_calculate_buffed_cost($action, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $actiondetails             = get_player_action($action, $userid);
    $active_action_buffs_class = stamina_get_active_buffs($actiondetails['class'], $userid, true);
    $active_action_buffs       = \array_merge(stamina_get_active_buffs($action, $userid), $active_action_buffs_class);

    $buffedcost = $actiondetails['naturalcostbase'];

    if (\is_array($active_action_buffs))
    {
        foreach ($active_action_buffs as $key => $values)
        {
            $buffedcost = $buffedcost * $values['costmod'];
        }
    }

    return $buffedcost;
}

/*
*******************************************************
CALCULATE EXPERIENCE GAIN
Returns the experience gained for the given action, taking buffs into account
*******************************************************
*/

function stamina_calculate_buffed_exp($action, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $actiondetails             = get_player_action($action, $userid);
    $active_action_buffs_class = stamina_get_active_buffs($actiondetails['class'] ?? '', $userid, true);
    $active_action_buffs       = \array_merge(stamina_get_active_buffs($action, $userid), $active_action_buffs_class);

    $buffedexp = e_rand(80, 120);

    if (\is_array($active_action_buffs) && $active_action_buffs)
    {
        foreach ($active_action_buffs as $buff => $values)
        {
            $buffedexp = \round($buffedexp * $values['expmod']);
        }
    }

    return $buffedexp;
}

/*
*******************************************************
GET ACTIVE BUFFS
Returns an array of buffs that relate to a particular action, or class of actions.
$isClass determine if action is a class (category) and return only all buff with this class
*******************************************************
*/

function stamina_get_active_buffs($action, $userid = false, $isClass = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $bufflist = \unserialize(get_module_pref('buffs', 'staminasystem', $userid));

    $active_action_buffs = [];

    if (\is_array($bufflist))
    {
        foreach ($bufflist as $buff => $values)
        {
            if (
                ( ! $isClass && ($values['action'] == $action || 'Global' == $values['action']))
                || ($isClass && $values['class'] == $action)
            ) {
                if ( ! isset($values['suspended']) || ! $values['suspended'])
                {
                    $active_action_buffs[$buff] = $values;
                }
            }
        }
    }

    return $active_action_buffs;
}

/*
*******************************************************
SUSPEND / RESTORE A BUFF / ALL BUFFS
Temporarily suspends a Stamina buff.  Restore it afterwards, because this is saved back to the modulepref.  God this needs baking into core and rewriting.
*******************************************************
*/

function suspend_stamina_buff($referencename, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    $bufflist = \unserialize(get_module_pref('buffs', 'staminasystem', $userid));

    if (\is_array($bufflist[$referencename]))
    {
        $bufflist[$referencename]['suspended'] = true;
        set_module_pref('buffs', \serialize($bufflist), 'staminasystem', $userid);
        $rtrue = true;
    }

    return (bool) ($rtrue)

     ;
}

function restore_stamina_buff($referencename, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    $bufflist = \unserialize(get_module_pref('buffs', 'staminasystem', $userid));

    if (\is_array($bufflist[$referencename]))
    {
        if ($bufflist[$referencename]['suspended'])
        {
            $bufflist[$referencename]['suspended'] = false;
            set_module_pref('buffs', \serialize($bufflist), 'staminasystem', $userid);
            $rtrue = true;
        }
    }

    return (bool) ($rtrue)

     ;
}

function restore_all_stamina_buffs($userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    $bufflist = \unserialize(get_module_pref('buffs', 'staminasystem', $userid));

    if (\is_array($bufflist) && \count($bufflist) > 0)
    {
        foreach ($bufflist as $buff => $values)
        {
            $bufflist[$buff]['suspended'] = false;
        }
    }
    //\LotgdResponse::pageDebug("restoring buffs");
    set_module_pref('buffs', \serialize($bufflist), 'staminasystem', $userid);
}

function mass_suspend_stamina_buffs($name, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    $bufflist = \unserialize(get_module_pref('buffs', 'staminasystem', $userid));
    // \LotgdResponse::pageDebug($bufflist);
    if (\is_array($bufflist) && \count($bufflist) > 0)
    {
        // \LotgdResponse::pageDebug("Okay, it's an array");
        foreach ($bufflist as $buff => $values)
        {
            if (false !== \strpos($buff, $name))
            {
                //\LotgdResponse::pageDebug("Suspending buff ".$buff);
                $bufflist[$buff]['suspended'] = true;
                $rtrue                        = true;
            }
        }
        set_module_pref('buffs', \serialize($bufflist), 'staminasystem', $userid);
    }

    return (bool) ($rtrue)

     ;
}

/*
*******************************************************
ADVANCE BUFFS
Removes a round from buffs related to the given action, then removes the buff from the array if rounds are zero.
Also outputs round and wearoff messages.
*******************************************************
*/

function stamina_advance_buffs($action, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $bufflist      = \unserialize(get_module_pref('buffs', 'staminasystem', $userid));
    $actiondetails = get_player_action($action, $userid);

    $write = 0;

    if (\is_array($bufflist))
    {
        foreach ($bufflist as $buff => $values)
        {
            if ($values['action'] == $action || 'Global' == $values['action'] || $values['class'] == $actiondetails['class'])
            {
                if ( ! isset($values['suspended']) || ! $values['suspended'])
                {
                    if (isset($values['roundmsg']) && $values['roundmsg'])
                    {
                        \LotgdResponse::pageAddContent(\LotgdFormat::colorize(\sprintf('%s`n', \stripslashes($values['roundmsg'])), true));
                    }

                    if ($values['rounds'] > 0)
                    {
                        --$values['rounds'];
                        $write = 1;
                    }

                    if (0 == $values['rounds'])
                    {
                        if ($values['wearoffmsg'])
                        {
                            \LotgdResponse::pageAddContent(\LotgdFormat::colorize(\sprintf('%s`n', \stripslashes($values['wearoffmsg'])), true));
                        }
                        $write = 1;
                        unset($bufflist[$buff]);
                    }
                    else
                    {
                        $bufflist[$buff] = $values;
                    }
                }
            }
        }
    }

    if ($write)
    {
        if (0 != \count($bufflist))
        {
            set_module_pref('buffs', \serialize($bufflist), 'staminasystem', $userid);
        }
        else
        {
            set_module_pref('buffs', 'a:0:{}', 'staminasystem', $userid);
        }
    }

    return true;
}

/*
*******************************************************
STRIP A BUFF
Removes a Stamina buff.
*******************************************************
*/

function strip_stamina_buff($buff, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    $bufflist = \unserialize(get_module_pref('buffs', 'staminasystem', $userid));

    if (\is_array($bufflist))
    {
        unset($bufflist[$buff]);
        set_module_pref('buffs', \serialize($bufflist), 'staminasystem', $userid);
    }
}

/*
*******************************************************
REMOVE ALL BUFFS
Empties the player's Buffs array.  Used at newday.
*******************************************************
*/

function stamina_strip_all_buffs($userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    set_module_pref('buffs', 'a:0:{}', 'staminasystem', $userid);

    return true;
}

/*
*******************************************************
GET DISPLAY COST
Returns a percentage of the player's total Stamina that is used when performing this action, by default to three decimal places.
*******************************************************
*/

function stamina_getdisplaycost($action, $precision = 2, $userid = false)
{
    global $session;
    $costval = stamina_calculate_buffed_cost($action, $userid);
    $costpct = \round(($costval / 2000000) * 100, $precision);

    return $costpct;
}

/*
*******************************************************
TAKE ACTION COST
Calculates buffs, removes stamina, and returns the amount taken.
*******************************************************
*/

function stamina_take_action_cost($action, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    $totalcost = stamina_calculate_buffed_cost($action, $userid);
    removestamina($totalcost, $userid);

    return $totalcost;
}

/**
 * Check if user has stamina for use action.
 *
 * @param string $action
 * @param int    $userid
 * @param int    $qty    Number of times the ability is to be used
 *
 * @return bool
 */
function stamina_check_can_use($action, $userid = false, $qty = 1)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $totalcost = stamina_calculate_buffed_cost($action, $userid) * $qty;

    $stamina = (int) get_module_pref('stamina', 'staminasystem', $userid);

    return (bool) ($totalcost <= $stamina)

     ;
}

/*
*******************************************************
AWARD EXPERIENCE
Calculates buffs, awards experience, returns experience awarded.
*******************************************************
*/

function stamina_award_exp($action, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $totalexp   = stamina_calculate_buffed_exp($action, $userid);
    $actionlist = get_player_action_list($userid);

    if (isset($actionlist[$action]['exp']))
    {
        $actionlist[$action]['exp'] += $totalexp;
    }
    else
    {
        $actionlist[$action]['exp'] = $totalexp;
    }

    set_module_pref('actions', \serialize($actionlist), 'staminasystem', $userid);

    return $totalexp;
}

/*
*******************************************************
PROCESS ACTION
Calculates buffs, awards experience, upgrades level, removes cost, advances buffs, returns Stamina used and Experience gained.
*******************************************************
*/

function process_action($action, $userid = false)
{
    global $session, $actions_used;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $info_to_return                = ['action' => $action, 'points_used' => 0, 'exp_earned' => 0];
    $info_to_return['points_used'] = stamina_take_action_cost($action, $userid);
    $info_to_return['exp_earned']  = stamina_award_exp($action, $userid);
    stamina_advance_buffs($action, $userid);
    $info_to_return['lvlinfo'] = stamina_level_up($action, $userid);

    if ( ! isset($actions_used[$action]))
    {
        $actions_used[$action]               = [];
        $actions_used[$action]['exp_earned'] = 0;
    }

    $actions_used[$action]['exp_earned'] += $info_to_return['exp_earned'];
    $actions_used[$action]['lvlinfo'] = $info_to_return['lvlinfo'];

    //We want to put a ladder of some sort in here, where the player can see the player above them in the HOF and the player below them as well.

    //-- Hook to aditional process
    modulehook('staminasystem-process-action', ['action' => $info_to_return]);

    return $info_to_return;
}

//
// GET STAMINA VALUES
// Returns the current Stamina values for the player.
// Syntax:
// get_stamina(type, realvalue, userid);
/*
Type:
0 = Red
1 (default) = Amber
2 = Green
3 = Total
4 = Starting value (will not return as percentage)

Realvalue:
false (default) = Returns a percentage value of total.
true = Returns actual value.

Example usage:

$stamina = get_stamina();
Will return a percentage of the amber stamina value for the current player, so that the module author can adjust the outcome based on how knackered the player is.
You can return the red, green and total values too.  Example:

$red = get_stamina(0);
Returns the red value as a percentage.

$green = get_stamina(2, 1);
Returns the green value in terms of actual Stamina points.

$total = get_stamina(3, 1);
Returns the player's total Stamina points.

*/

function get_stamina($type = 1, $realvalue = false, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $totalstamina = get_module_pref('stamina', 'staminasystem', $userid);
    $maxstamina   = 2000000;
    $totalpct     = ($totalstamina / $maxstamina) * 100;
    $redpoint     = get_module_pref('red', 'staminasystem', $userid);
    $amberpoint   = get_module_pref('amber', 'staminasystem', $userid);

    $greenmax   = $maxstamina   - $redpoint   - $amberpoint;
    $greenvalue = $totalstamina - $redpoint - $amberpoint;
    $greenpct   = ($greenvalue / $greenmax) * 100;

    if ($greenvalue < 0)
    {
        $greenvalue = 0;
        $greenpct   = 0;
    }

    $ambermax   = $amberpoint;
    $ambervalue = $totalstamina - $redpoint;
    $amberpct   = ($ambervalue / $ambermax) * 100;

    if ($ambervalue < 0)
    {
        $ambervalue = 0;
        $amberpct   = 0;
    }

    if ($ambervalue > $amberpoint)
    {
        $ambervalue = $amberpoint;
        $amberpct   = 100;
    }

    $redmax   = $redpoint;
    $redvalue = $totalstamina;
    $redpct   = ($redvalue / $redmax) * 100;

    if ($redvalue > $redpoint)
    {
        $redvalue = $redpoint;
        $redpct   = 100;
    }

    switch ($type)
    {
        case 1:
            $returnvalue = $ambervalue;

            if (false === $realvalue)
            {
                $returnvalue = $amberpct;
            }

        break;

        case 2:
            $returnvalue = $greenvalue;

            if (false === $realvalue)
            {
                $returnvalue = $greenpct;
            }

        break;

        case 3:
            $returnvalue = $totalstamina;

            if (false === $realvalue)
            {
                $returnvalue = $totalpct;
            }

        break;
        case 4:
            $returnvalue = $maxstamina;

        break;

        case 0:
        default:
            $returnvalue = $redvalue;

            if (false === $realvalue)
            {
                $returnvalue = $redpct;
            }

        break;
    }

    return $returnvalue;
}

/**
 * Check if the action have all necesary options.
 *
 * @param string $action
 * @param array  $actions
 * @param bool   $userid
 *
 * @return bool
 */
function stamina_check_action($action, $actions, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    //-- Generate action for player
    if ( ! isset($actions[$action]))
    {
        $defaultactions = get_default_action_list();

        if (isset($defaultactions[$action]))
        {
            $actions[$action]                    = $defaultactions[$action];
            $actions[$action]['lvl']             = 0;
            $actions[$action]['exp']             = 0;
            $actions[$action]['levelledup']      = false;
            $actions[$action]['naturalcost']     = $defaultactions[$action]['maxcost'];
            $actions[$action]['naturalcostbase'] = $defaultactions[$action]['maxcost'];

            set_module_pref('actions', \serialize($actions), 'staminasystem', $userid);
        }

        return false;
    }
    else
    {
        if (
            ! isset($actions[$action]['naturalcostbase']) || ! isset($actions[$action]['naturalcost']) || ! isset($actions[$action]['firstlvlexp']) || ! isset($actions[$action]['expincrement']) || ! isset($actions[$action]['costreduction']) || $actions[$action]['lvl'] > 100
        ) {
            $defaultactions = get_default_action_list();

            $exp                                 = $actions[$action]['exp'] ?? 0;
            $actions[$action]                    = $defaultactions[$action];
            $actions[$action]['lvl']             = 0;
            $actions[$action]['exp']             = $exp;
            $actions[$action]['levelledup']      = false;
            $actions[$action]['naturalcost']     = $defaultactions[$action]['maxcost'];
            $actions[$action]['naturalcostbase'] = $defaultactions[$action]['maxcost'];

            set_module_pref('actions', \serialize($actions), 'staminasystem', $userid);

            return false;
        }
    }

    return true;
}

/*
*******************************************************
LEVEL UP
Determines whether the player is ready to level up, levels up if appropriate, returns start and end of EXP range for this level OR player has levelled up.
*******************************************************
*/

function stamina_level_up($action, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $actions = get_player_action_list($userid);

    //-- No need level up, are maxed
    if ($actions[$action]['lvl'] >= 100)
    {
        return false;
    }

    $returninfo               = [];
    $returninfo['class']      = $actions[$action]['class'] ?? '';
    $returninfo['levelledup'] = false;
    $stop                     = 0;

    if ( ! stamina_check_action($action, $actions, $userid))
    {
        return stamina_level_up($action, $userid);
    }

    while (0 == $stop)
    {
        $currentexp = ($actions[$action]['exp'] ?? 0);
        $currentlvl = $actions[$action]['lvl'];
        $first      = $actions[$action]['firstlvlexp'];
        $increment  = $actions[$action]['expincrement'];
        $stop       = 1;
        //Determine the next level's EXP requirements
        $addup = [0 => $first];

        for ($i = 1; $i <= 100; ++$i)
        {
            $addup[$i] = \round($addup[$i - 1] * $increment);
        }

        $levels = [0 => $first];

        for ($i = 1; $i <= 100; ++$i)
        {
            $levels[$i] = ($levels[$i - 1] + $addup[$i]);
        }

        $currentlvlexp = 0;

        if ($currentlvl > 0)
        {
            $currentlvlexp = $levels[$currentlvl - 1];
        }

        $nextlvlexp = $levels[$currentlvl];

        $returninfo['exp']           = $currentexp;
        $returninfo['lvl']           = $currentlvl;
        $returninfo['nextlvlexp']    = $nextlvlexp;
        $returninfo['currentlvlexp'] = $currentlvlexp;

        //Check if player's exp is more than level requirement, and level up if true
        if ($currentexp > $nextlvlexp && $actions[$action]['lvl'] < 100)
        {
            $stop = 0;
            //level up
            ++$actions[$action]['lvl'];
            //reduce costs
            $actions[$action]['naturalcost'] -= $actions[$action]['costreduction'];
            $actions[$action]['naturalcostbase'] = $actions[$action]['naturalcost'];
            //set "levelledup" to true, so that the module can output levelling up text
            $returninfo['levelledup'] = true;
            $returninfo['newlvl']     = $actions[$action]['lvl'];
        }
    }

    //write back array to modulepref
    set_module_pref('actions', \serialize($actions), 'staminasystem', $userid);

    return $returninfo;
}

/*
*******************************************************
LEVEL DOWN
Determines whether the player is ready to level up, levels up if appropriate, returns start and end of EXP range for this level OR player has levelled up.
*******************************************************
*/
function stamina_level_down($action, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $returninfo = [];
    $stop       = 0;

    $actions = get_player_action_list($userid);

    if ($actions[$action]['lvl'] >= 100)
    {
        return false;
    }

    while (0 == $stop)
    {
        $actions    = get_player_action_list($userid);
        $currentexp = $actions[$action]['exp'];
        $currentlvl = $actions[$action]['lvl'];
        $first      = $actions[$action]['firstlvlexp'];
        $increment  = $actions[$action]['expincrement'];
        $stop       = 1;
        //Determine the next level's EXP requirements
        $addup    = [];
        $addup[0] = $first;

        for ($i = 1; $i <= 100; ++$i)
        {
            $addup[$i] = \round($addup[$i - 1] * $increment);
        }

        $levels    = [];
        $levels[0] = $first;

        for ($i = 1; $i <= 100; ++$i)
        {
            $levels[$i] = ($levels[$i - 1] + $addup[$i]);
        }

        $currentlvlexp = 0;

        if (0 != $currentlvl)
        {
            $currentlvlexp = $levels[$currentlvl];
        }

        $nextlvlexp    = $levels[$currentlvl];
        $currentlvlexp = $levels[$currentlvl - 1];

        $returninfo['exp']           = $currentexp;
        $returninfo['lvl']           = $currentlvl;
        $returninfo['nextlvlexp']    = $nextlvlexp;
        $returninfo['currentlvlexp'] = $currentlvlexp;

        //Check if player's exp is more than level requirement, and level up if true
        if ($currentexp > $nextlvlexp && $actions[$action]['lvl'] <= 100)
        {
            $stop = 0;
            //level up
            ++$actions[$action]['lvl'];
            //reduce costs
            $actions[$action]['naturalcost'] -= $actions[$action]['costreduction'];
            $actions[$action]['naturalcostbase'] = $actions[$action]['naturalcost'];
            //write back array to modulepref
            set_module_pref('actions', \serialize($actions), 'staminasystem', $userid);
            //set "levelledup" to true, so that the module can output levelling up text
            $returninfo['levelledup'] = true;
            $returninfo['newlvl']     = $actions[$action]['lvl'];
        }
    }

    return $returninfo;
}

/*
*******************************************************
PROCESS NEW DAY
Strips buffs, resets Stamina to starting value.
*******************************************************
*/

function stamina_process_newday($userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    modulehook('stamina-newday-intercept');
    // remove buffs
    stamina_strip_all_buffs($userid);

    $startingstamina = 2000000;
    set_module_pref('stamina', $startingstamina, 'staminasystem', $userid);
    set_module_pref('amber', 500000, 'staminasystem', $userid);
    set_module_pref('red', 300000, 'staminasystem', $userid);

    modulehook('stamina-newday');

    return true;
}

/*
*******************************************************
ADD AND REMOVE STAMINA
Simple functions to add or remove Stamina from players.
*******************************************************
*/

function addstamina($amount, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }
    $newstamina = get_module_pref('stamina', 'staminasystem', $userid) + $amount;
    set_module_pref('stamina', $newstamina, 'staminasystem', $userid);

    return $newstamina;
}

function removestamina($amount, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $newstamina = get_module_pref('stamina', 'staminasystem', $userid) - $amount;
    $newstamina = \max($newstamina, 0);

    set_module_pref('stamina', $newstamina, 'staminasystem', $userid);

    return $newstamina;
}

function stamina_minihof($action, $userid = false)
{
    global $session;

    $cache = \LotgdKernel::get('cache.app');

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $st            = \microtime(true);
    $boardfilename = \LotgdSanitize::slugify($action);
    $item          = $cache->getItem('modules_stamina_boardinfo_'.$boardfilename);
    $en            = \microtime(true);
    $to            = $en - $st;

    if ( ! $item->isHit())
    {
        $board = [];

        $query         = \Doctrine::createQueryBuilder();
        $staminaresult = $query->from('LotgdCore:ModuleUserprefs', 'u')
            ->select('u.setting', 'u.value', 'u.userid')

            ->where('u.modulename = :module AND u.setting = :setting')

            ->setParameters([
                'module'   => 'staminasystem',
                'setting' => 'actions',
            ])

            ->getQuery()
            ->getResult()
        ;

        foreach ($staminaresult as $row)
        {
            $actions_array = @\unserialize($row['value']);

            if ( ! isset($actions_array[$action]))
            {
                continue;
            }

            $actiondetails         = $actions_array[$action];
            $board[$row['userid']] = $actiondetails['exp'] ?? 0;
        }

        $boardinfo = stamina_minihof_assignranks($board);

        $item->set($boardinfo);
        $cache->save($item);
    }

    $boardinfo = $item->get();

    //set the player's entry in the board with brand-new data
    $player_action               = get_player_action($action);
    $boardinfo['board'][$userid] = $player_action['exp'];

    $smallboard = stamina_minihof_makesmallboard($boardinfo, $userid);

    if ( ! $smallboard)
    {
        $boardinfo  = stamina_minihof_assignranks($boardinfo['board']);
        $smallboard = stamina_minihof_makesmallboard($boardinfo, $userid);
    }

    $params = [
        'textDomain' => 'module_staminasystem',
        'board'      => $smallboard,
        'action'     => $action,
        'userId'     => $userid,
    ];

    //display the board!

    \LotgdResponse::pageAddContent(LotgdTheme::render('@module/staminasystem/lib/minihof.twig', $params));
}

function stamina_minihof_makesmallboard($boardinfo, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $ranks      = $boardinfo['ranks'];
    $board      = $boardinfo['board'];
    $smallboard = [];

    $st     = \microtime(true);
    $myrank = \array_search($userid, $ranks);
    $en     = \microtime(true);
    $to     = $en - $st;

    $st = \microtime(true);
    //get the twenty players above and below, put them in arrays
    $largeboard = [];

    for ($i = -10; $i <= 10; ++$i)
    {
        if ( ! isset($ranks[$myrank + $i]))
        {
            continue;
        }

        $acctid           = $ranks[$myrank + $i];
        $parray           = [];
        $parray['acctid'] = $acctid;
        $parray['xp']     = $board[$acctid];
        $parray['rank']   = $myrank + $i;

        if ($acctid == $session['user']['acctid'])
        {
            $parray['name'] = $session['user']['name'];
        }

        if ($parray['acctid'])
        {
            $largeboard[] = $parray;
        }
    }
    $en = \microtime(true);
    $to = $en - $st;

    //now, check the player's rank is where it should be.  If not, re-work the ranks.  If the player is off the page, run assignranks and start again.

    $playerposition = $myrank;

    if ($myrank >= 10)
    {
        $playerposition = 10;
    }

    $st        = \microtime(true);
    $redoranks = false;

    while (isset($largeboard[$playerposition]['xp']) && $largeboard[$playerposition]['xp'] > $largeboard[$playerposition - 1]['xp'] && $playerposition >= 0)
    {
        $temp                            = $largeboard[$playerposition];
        $largeboard[$playerposition]     = $largeboard[$playerposition - 1];
        $largeboard[$playerposition - 1] = $temp;
        --$playerposition;
        $redoranks = true;
    }

    if ($playerposition <= 0)
    {
        return false;
    }
    $en = \microtime(true);
    $to = $en - $st;
    //now recalc the ranks, from top to bottom
    if ($redoranks && $playerposition)
    {
        $startrank = $largeboard[0]['rank'];

        for ($i = 1; $i <= 20; ++$i)
        {
            $largeboard[$i]['rank'] = $startrank + $i;
        }
    }

    $smallboard = [];

    for ($i = -2; $i <= 2; ++$i)
    {
        if (isset($largeboard[$playerposition + $i]) && $largeboard[$playerposition + $i])
        {
            $smallboard[] = $largeboard[$playerposition + $i];
        }
    }

    $st = \microtime(true);
    //get the names of the contestants in the small board
    $sbc = \count($smallboard);

    $query = \Doctrine::createQueryBuilder();
    $query->from('LotgdCore:Avatar', 'u')
        ->select('u.name')

        ->where('u.acct = :id')
    ;

    for ($i = 0; $i < $sbc; ++$i)
    {
        if ( ! isset($smallboard[$i]['name']) || ! $smallboard[$i]['name'])
        {
            $sql = clone $query;
            $sql->setParameter('id', $smallboard[$i]['acctid']);
            $smallboard[$i]['name'] = $sql->getQuery()->getSingleScalarResult();
        }
    }
    $en = \microtime(true);
    $to = $en - $st;

    return $smallboard;
}

function stamina_minihof_assignranks($board)
{
    \arsort($board);
    $r     = 1;
    $ranks = [];
    $st    = \microtime(true);

    foreach ($board as $acctid => $exp)
    {
        $ranks[$r] = $acctid;
        ++$r;
    }
    $en                 = \microtime(true);
    $to                 = $en - $st;
    $boardinfo          = [];
    $boardinfo['board'] = $board;
    $boardinfo['ranks'] = $ranks;

    return $boardinfo;
}

function stamina_minihof_old($action, $userid = false)
{
    global $session;

    $cache = \LotgdKernel::get('cache.app');

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $st = \microtime(true);

    $boardfilename = \str_replace(' ', '', $action);
    $item          = $cache->getItem('modules/stamina/boardinfo_'.$boardfilename);

    $en = \microtime(true);
    $to = $en - $st;
    \LotgdResponse::pageDebug('Cache: '.$to);

    if ( ! $item->isHit())
    {
        $board = [];

        $query         = \Doctrine::createQueryBuilder();
        $staminaresult = $query->from('LotgdCore:ModuleUserprefs', 'u')
            ->select('u.setting', 'u.value', 'u.userid')

            ->where('u.modulename = :module AND u.setting = :setting')

            ->setParameters([
                'module'   => 'staminasystem',
                'settings' => 'actions',
            ])

            ->getQuery()
            ->getResult()
        ;

        foreach ($staminaresult as $row)
        {
            $actions_array = @\unserialize($row['value']);
            $actiondetails = $actions_array[$action];

            if ( ! $actiondetails['exp'])
            {
                continue;
            }
            $board[$row['userid']]['xp'] = $actiondetails['exp'];
            $board[$row['userid']]['id'] = $row['userid'];
        }

        $boardinfo = stamina_minihof_assignranks($board);

        $item->set($boardinfo);
        $cache->save($item);
    }

    $boardinfo = $item->get();
    //set the player's entry in the board with brand-new data
    $player_action                     = get_player_action($action);
    $boardinfo['board'][$userid]['xp'] = $player_action['exp'];

    $smallboard = stamina_minihof_smallboard($boardinfo, $userid);

    \LotgdResponse::pageDebug($smallboard);
}

function stamina_minihof_sort($x, $y)
{
    if ($x['xp'] == $y['xp'])
    {
        return 0;
    }
    elseif ($x['xp'] < $y['xp'])
    {
        return 1;
    }

    return -1;
}

function stamina_minihof_smallboard_old($boardinfo, $userid = false)
{
    global $session;

    if (false === $userid)
    {
        $userid = $session['user']['acctid'];
    }

    $ranks      = $boardinfo['ranks'];
    $board      = $boardinfo['board'];
    $smallboard = [];

    $myrank = \array_search($userid, $ranks);

    $smallboard[] = $board[$ranks[$myrank - 2]];
    $smallboard[] = $board[$ranks[$myrank - 1]];
    $smallboard[] = $board[$ranks[$myrank]];
    $smallboard[] = $board[$ranks[$myrank + 1]];
    $smallboard[] = $board[$ranks[$myrank + 2]];

    if ($smallboard[2]['xp'] > $smallboard[1]['xp'])
    {
        \LotgdResponse::pageDebug('resorting...');
        \usort($smallboard, 'stamina_minihof_sort');
    }

    return $smallboard;
}

function stamina_minihof_assignranks_old($board)
{
    \uasort($board, 'stamina_minihof_sort');
    $r     = 1;
    $ranks = [];
    $st    = \microtime(true);

    foreach ($board as $acctid => $vals)
    {
        $ranks[$r] = $acctid;
        ++$r;
    }
    $en = \microtime(true);
    $to = $en - $st;
    \LotgdResponse::pageDebug("Rank assignment: {$to}");
    $boardinfo          = [];
    $boardinfo['board'] = $board;
    $boardinfo['ranks'] = $ranks;
    \LotgdResponse::pageDebug($ranks);

    return $boardinfo;
}
