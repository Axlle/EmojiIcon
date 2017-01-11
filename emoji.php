<?php

function utf8_to_unicode($c)
{
    $ord0 = ord($c{0}); if ($ord0>=0   && $ord0<=127) return $ord0;
    $ord1 = ord($c{1}); if ($ord0>=192 && $ord0<=223) return ($ord0-192)*64 + ($ord1-128);
    $ord2 = ord($c{2}); if ($ord0>=224 && $ord0<=239) return ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
    $ord3 = ord($c{3}); if ($ord0>=240 && $ord0<=247) return ($ord0-240)*262144 + ($ord1-128)*4096 + ($ord2-128)*64 + ($ord3-128);
    return false;
}
