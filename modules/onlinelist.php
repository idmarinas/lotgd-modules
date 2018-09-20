<?php

function onlinelist_getmoduleinfo()
{
    return [
        'name' => 'Alternative Sorting',
        'author' => 'Christian Rutsch',
        'version' => '1.2',
        'category' => 'Administrative',
        'download' => 'http://dragonprime.net/users/XChrisX/onlinelist.zip',
        'allowanonymous' => 1,
    ];
}

function onlinelist_install()
{
    module_addhook('onlinecharlist');

    return true;
}

function onlinelist_uninstall()
{
    return true;
}

function onlinelist_dohook($hookname, $args)
{
    switch ($hookname) {
        case 'onlinecharlist':
            $args['handled'] = true;
            $list_mods = '';
            $list_players = '';

            //-- Staff users
            $select = DB::select('accounts');
            $select->columns(['name'])
                ->order('superuser DESC, level DESC')
                ->where->equalTo('locked', 0)
                    ->equalTo('loggedin', 1)
                    ->greaterThan('superuser', 0)
                    ->greaterThan('laston', date('Y-m-d H:i:s', strtotime('-'.getsetting('LOGINTIMEOUT', 900).' seconds')))
            ;
            $result = DB::execute($select);

            $list_mods = appoencode(sprintf(translate_inline('`bOnline Staff`n(%s Staff Member):`b`n'), $result->count()));
            $onlinecount_mods = $result->count();

            if ($result->count())
            {
                foreach ($result as $key => $value)
                {
                    $list_mods .= appoencode("`%{$value['name']}`0`n");
                }
            }
            else
            {
                $list_mods .= appoencode(translate_inline('`iNone`i'));
            }

            //-- Normal users
            $select = DB::select('accounts');
            $select->columns(['name'])
                ->order('level DESC')
                ->where->equalTo('locked', 0)
                    ->equalTo('loggedin', 1)
                    ->equalTo('superuser', 0)
                    ->greaterThan('laston', date('Y-m-d H:i:s', strtotime('-'.getsetting('LOGINTIMEOUT', 900).' seconds')))
            ;
            $result = DB::execute($select);

            $list_players = appoencode(sprintf(translate_inline('`bCharacters Online`n(%s Players):`b`n'), $result->count()));
            $onlinecount_players = $result->count();

            if ($result->count())
            {
                $row = array_slice(DB::toArray($result), 0, 10);//-- Only show 5 users
                foreach ($row as $key => $value)
                {
                    $list_players .= appoencode("`^{$value['name']}`0`n");
                }

                if ($result->count() > 5)
                {
                    $list_players .= appoencode(sprintf(translate_inline('`$...`nand %s Players more`0`n'), ($result->count() - 10)));
                }
            }
            else
            {
                $list_players .= appoencode(translate_inline('`iNone`i'));
            }

            $args['list'] = $list_mods.'<br>'.$list_players;
            $args['count'] = $onlinecount_mods + $onlinecount_players;
        break;
    }

    return $args;
}

function onlinelist_run()
{
}
