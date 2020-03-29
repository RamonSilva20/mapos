<?php

if (!function_exists("dd")) {
    function dd($var, ...$moreVars)
    {
        dump($var, $moreVars);
        die();
    }
}
