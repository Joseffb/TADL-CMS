<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 10:26 AM
 */

namespace utils;


class debug {
    static function pe($stuff) {
        //pretty echo
        $fw = \Base::instance();
        if($fw->DEBUG > 0) {
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            echo "<em>called from ".$caller['file']. " line:".$caller['line'].'<br/></em>';
            echo "<pre>";
            var_dump($stuff);
            echo "<pre>";
        }
    }
}
