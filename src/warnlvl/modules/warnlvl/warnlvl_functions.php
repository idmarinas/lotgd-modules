<?php

function get_suffix($num)
{
    // Function written by Marcus L. Griswold (vujsa)
    // Can be found at http://www.handyphp.com
    // Do not remove this header!

    if (is_numeric($num))
    {
        if (11 == substr($num, -2, 2) || 12 == substr($num, -2, 2) || 13 == substr($num, -2, 2))
        {
            $suffix = 'th';
        }
        elseif (1 == substr($num, -1, 1))
        {
            $suffix = 'st';
        }
        elseif (2 == substr($num, -1, 1))
        {
            $suffix = 'nd';
        }
        elseif (3 == substr($num, -1, 1))
        {
            $suffix = 'rd';
        }
        else
        {
            $suffix = 'th';
        }

        return $num.$suffix;
    }
    else
    {
        return null;
    }
}

function time_since($original)
{
    // Function written by skyhawk133 - March 2, 2005
    // http://www.dreamincode.net/code/snippet86.htm

    // array of time period chunks
    $chunks = [
        [60 * 60 * 24 * 365, 'year'],
        [60 * 60 * 24 * 30, 'month'],
        [60 * 60 * 24 * 7, 'week'],
        [60 * 60 * 24, 'day'],
        [60 * 60, 'hour'],
        [60, 'minute'],
    ];

    $today = time();
    $since = $today - $original;

    // $j saves performing the count function each time around the loop
    for ($i = 0, $j = count($chunks); $i < $j; $i++)
    {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];

        // finding the biggest chunk (if the chunk fits, break)
        if (0 != ($count = floor($since / $seconds)))
        {
            break;
        }
    }

    $print = (1 == $count) ? '1 '.$name : "$count {$name}s";

    if ($i + 1 < $j)
    {
        // now getting the second item
        $seconds2 = $chunks[$i + 1][0];
        $name2 = $chunks[$i + 1][1];

        // add second item if it's greater than 0
        if (0 != ($count2 = floor(($since - ($seconds * $count)) / $seconds2)))
        {
            $print .= (1 == $count2) ? ', 1 '.$name2 : ", $count2 {$name2}s";
        }
    }

    return $print;
}
