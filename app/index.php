<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 9:48 AM
 */

class index {
    function root($f3) {
        $d = new \lib\users_tbl();
        $d->setup();
        echo "<h3>This is a home test.</h3>";
    }

}
