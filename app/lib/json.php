<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 11:34 AM
 */

namespace lib;

class json extends core_cm {
    function request() {
        echo "hi request";
    }

    function get()
    {

    }

    function post()
    {
    }

    function put()
    {
    }

    function delete()
    {
    }

    function error (){
        utils::send_json(404);
    }
}