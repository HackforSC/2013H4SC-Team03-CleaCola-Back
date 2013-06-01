<?php

namespace CC\Helper;

class Partial
{
    public static function render($partial, $values = array())
    {
        include realpath(dirname(__FILE__) . '/../') . '/partials/' . $partial;
    }
}