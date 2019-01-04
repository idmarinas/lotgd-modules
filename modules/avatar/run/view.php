<?php

$set = httpget('set');
$cost = get_module_setting('cost');
$ccost = get_module_setting('changecost');
addnav('Purchase');
if (! get_module_pref('bought'))
{
    addnav(['Purchase Avatar (%s %s)', $cost,
        translate_inline(1 == $cost ? 'point' : 'points')],
        "runmodule.php?module=avatar&op=purchase&cost=$cost");
}
else
{
    addnav(['Change Avatar (%s %s)', $ccost,
        translate_inline(1 == $ccost ? 'point' : 'points')],
            "runmodule.php?module=avatar&op=purchase&cost=$ccost");
}
if (! $set)
{
    output('As you look around the room, you see different groups of images.');
    output('Which one would you like to look at?`n`n');
    avatar_showsets();
}
else
{
    output('You step over to view the set of images which caught your eye.`n`n');
    avatar_showimages($set);
    avatar_showsets();
}
