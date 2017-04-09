<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 4/1/17
 * Time: 8:43 AM
 */

namespace extensions\admin;


class ext_test
{
    function __construct()
    {
        $this->one();
    }

    function install()
    {
        $this->one();
    }

    static function  one()
    {
        $fw = \Base::instance();

        //echo '<pre>';
        //echo debug_print_backtrace();
       // echo '</pre>';

        //This is a test class to test out the config system. File soon to be deleted.
        echo "<h2>SCREEN PRINT: Extension test:<br/> $fw->TESTSYSTEMVAR</h2>";
    }
}