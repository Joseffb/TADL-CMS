<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 11:34 AM
 */

namespace controllers;

use utils\debug;
use utils\http;

class json extends \core\controller_model
{
    function request()
    {
        echo "hi request";
    }

    function get()
    {
        return $this->process_request();
    }

    function post()
    {
        return $this->process_request();
    }

    function put()
    {
        return $this->process_request();
    }

    function delete()
    {
        return $this->process_request();
    }

    function process_request($protocol = "GET")
    {
        $props = http::get_params();
        $wadl = new wadl();
        $w = $wadl->get_wadl('public');
        unset($props[0]);
        unset($props[1]);
        $namespace = "controllers"; //default namespace
        $controller = array_shift($props);
        $method = array_shift($props);;
        //debug::pe($w[$namespace][$controller]['methods'][$method]);
        //get namespace
        if (!empty($w[$namespace][$controller]['methods'][$method])) {
            $mhd = $w[$namespace][$controller]['methods'][$method];
            if (!in_array($protocol, $mhd['protocols'])) {
                $retVal = false;
            } else {
                $retVal = $mhd;
                $retVal['method'] = $method;
            }
        } else {
            foreach ($w as $namespace => $con) {

                foreach ($con as $c => $attrib) {
                    if (!empty($attrib['methods'][$method])) {
                        $mhd = $attrib['methods'][$method];
                        if (!in_array($protocol, $mhd['protocols'])) {
                            $retVal = false;
                        } else {
                            $retVal = $mhd;
                            $retVal['method'] = $method;
                        }
                    }
                }
            }
        }

        $cnt = count($props);
        $args = null;
        if ($retVal) {
            if ($cnt > 0) {
                //I got args! we send these as array -- the receiving function must process them.
                $args = $props;
            }
            $class = "\\" . $retVal['namespace'] . "\\" . $retVal['controller'];
            $c = new $class();
            $method = $retVal['method'];

            return $c->$method($args);
        }
        return $retVal;

    }

    function error()
    {
        \utils\json::send_json(404);
    }
}
