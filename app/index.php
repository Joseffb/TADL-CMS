<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 9:48 AM
 */

class index {
    function root($f3) {
        $d = new \tables\users();
        echo $f3->db->log();
        //$d->setup();
        echo "<h3>This is a home test.</h3>";


    }

}
