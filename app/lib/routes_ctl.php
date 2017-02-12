<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 9:48 AM
 *
 * Core cms routes.
 * These can be changed/overridden in the routes ini.
 *
 */

namespace lib;

class routes_ctl extends core_cm {

    public function __construct(  ) {
        parent::__construct();

    }

    public function check_if_route_exist ($route) {
        $retVal = false;
        if (array_key_exists($route , $this->fw->ROUTES)) {
            $retVal = true;
        }
        return $retVal;
    }

    public function check_if_route_name_exist ($name, $protocol = "GET") {
        $retVal = false;
        foreach ($this->fw->ROUTES as $route) {
            if(strtolower($route[$protocol][3]) == strtolower($name));
                $retVal = $route[$protocol][0];
                break;
        }
        return $retVal;
    }


}