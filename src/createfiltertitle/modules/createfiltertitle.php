<?php

// this is a module that filters out new names at character creation that
// either begin or end with a title awarded for dragon kills, or lack thereof

function createfiltertitle_getmoduleinfo()
{
    return [
        'name'     => 'Title Filter at Creation',
        'category' => 'Administrative',
        'author'   => 'dying, refactoring by `%IDMarinas`0, <a href="//draconia.infommo.es">draconia.infommo.es</a>',
        'version'  => '1.1.0',
        'download' => 'core_module',
        'requires' => [
            'lotgd' => '>=4.11.0|Need a version equal or greater than 4.11.0 IDMarinas Edition',
        ],
    ];
}

function createfiltertitle_install()
{
    module_addhook('check-create');

    return true;
}

function createfiltertitle_uninstall()
{
    return true;
}

function createfiltertitle_dohook($hookname, $args)
{
    $textDomain = 'module_createfiltertitle';

    switch ($hookname)
    {
        case 'check-create':
            if ( ! isset($args['name']))
            {
                return $args;
            } //-- If not defined not check nothing

            $repository = \Doctrine::getRepository('LotgdCore:Titles');
            $result     = $repository->findAll();
            $titles     = $repository->extractEntity($result);

            $name = \str_replace(' ', '', $args['name']);

            foreach ($titles as $title)
            {
                if (hasCoreTitle($name, $title['female']))
                {
                    $args['blockaccount'] = 1;

                    \LotgdFlashMessages::addErrorMessage(\LotgdTranslator::t('flash.message.error', ['title' => $title['female']], $textDomain));
                }

                if (hasCoreTitle($name, $title['male']))
                {
                    $args['blockaccount'] = 1;

                    \LotgdFlashMessages::addErrorMessage(\LotgdTranslator::t('flash.message.error', ['title' => $title['male']], $textDomain));
                }
            }
        break;
    }

    return $args;
}

/**
 * Check if name have any title.
 *
 * @param string $name
 * @param string $title
 */
function hasCoreTitle($name, $title): bool
{
    $tf = \str_replace(' ', '', $title);
    $f1 = '/^'.$tf.'/i';
    $f2 = '/'.$tf.'$/i';

    if ((\preg_match($f1, $name) > 0) || (\preg_match($f2, $name) > 0))
    {
        return true;
    }

    return false;
}
