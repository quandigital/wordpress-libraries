<?php

namespace QuanDigital\WpLib;

class Sanitizer
{
    public function name($string)
    {
        return str_replace(['ä', 'ü', 'ö', 'ß'], ['ae', 'ue', 'oe', 'ss'], preg_replace('%\h%', '-', strtolower((string) $string)));
    }

    public function permalink($string)
    {
        return str_replace(['ä', 'ü', 'ö', 'ß', ' '], ['ae', 'ue', 'oe', 'ss', '%20'], strtolower((string) $string));
    }
}