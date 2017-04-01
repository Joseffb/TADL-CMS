<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 4/1/17
 * Time: 8:43 AM
 */

namespace extensions;


class test
{
    function __construct()
    {
        $this->one();
    }

    static function  one()
    {
        $fw = \Base::instance();

        //echo '<pre>';
        //echo debug_print_backtrace();
       // echo '</pre>';
        echo "<h2>SCREEN PRINT: Extension test: $fw->TESTSYSTEMVAR</h2>";
    }
}