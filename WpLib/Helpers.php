<?php

namespace QuanDigital\WpLib;

class Helpers
{
    public static function notEmpty($var)
    {
        return (isset($var) && !empty($var));
    }

    public static function log($log, $exit = false)
    {
        $trace = debug_backtrace();
        error_log(date('H:i:s', strtotime('now')) . ' ' . $trace[0]['file'] . ':' . $trace[0]['line'] . "\n" . print_r($log, true) . "\n", 3, WP_CONTENT_DIR . '/debug.log');
        if ($exit) {
            wp_die();
        }
    }
}