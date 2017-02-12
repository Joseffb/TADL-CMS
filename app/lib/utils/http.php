<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 10:26 AM
 */

namespace utils;


class http {
    static function get_params ($int = false) {
        $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri_segments = explode('/', $uri_path);
        if ($int && is_int($int)) {
            $retVal = $uri_segments[$int];
        } else {
            $retVal = $uri_segments;
        }
        return $retVal;
    }
}
