<?php

/**
 * Unblock Modules.
 *
 * Description: Special module to unlock modules that are blocked
 * For use this module you need create a file 'modules/idmarinas/unblockmodules/modules.php' with all modules you want unblock
 *
 * Versions:
 * - 1.0.0: Creation of module
 * - 1.0.1: Optimization
 */

/**
 * Get information of module.
 *
 * @return array
 */
function unblockmodules_getmoduleinfo()
{
    return [
        'name' => 'Unblock Modules',
        'version' => '1.0.1',
        'author' => '`%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'category' => 'Administrative',
        'description' => 'For use this module you need create a file "modules/idmarinas/unblockmodules/modules.php" with all modules you want unblock',
        'download' => 'https://bitbucket.org/idmarinas/lotgd-modules'
    ];
}

/**
 * Install module.
 *
 * @return bool
 */
function unblockmodules_install()
{
    module_addhook('newbieisland-everyhit-loggedin');

    return true;
}

/**
 * Uninstall module.
 *
 * @return bool
 */
function unblockmodules_uninstall()
{
    return true;
}

/**
 * Activate hook of module.
 *
 * @param string $hookname
 * @param array  $args
 *
 * @return array
 */
function unblockmodules_dohook($hookname, $args)
{
    global $session;

    if ('newbieisland-everyhit-loggedin' == $hookname && file_exists('modules/idmarinas/unblockmodules/modules.php'))
    {
        require_once 'modules/idmarinas/unblockmodules/modules.php';
    }

    return $args;
}

/**
 * URL of module.
 *
 * @param string $url
 *
 * @return string
 */
function unblockmodules_moduleurl($url = '')
{
    return "runmodule.php?module=unblockmodules{$url}";
}
