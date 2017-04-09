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

    static function write_log($msg, $die = false, $type = 'info') {
        $fw = \Base::instance();
        $prefix = array(
            'error' => '!!!! ERROR !!!!: ',
            'info' => '^INFO: ',
            'debug' => "###### DEBUG MESSAGE: ",
        );
        if(is_array($msg)) {
            $msg = $fw->stringify($msg);
        }
        $msg = $prefix[$type].$msg;

        error_log($msg);

        if($die) {
            die($msg);
        }
    }
}
