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

class json extends \core\controller
{
    function request()
    {
        echo "hi request";
    }

    function get()
    {
        return $this->process_request('GET');
    }

    function post()
    {
        return $this->process_request('POST');
    }

    function put()
    {
        return $this->process_request('PUT');
    }

    function delete()
    {
        return $this->process_request('DELETE');
    }

    function process_request($protocol = "ALL")
    {
        $fw = $this->fw;
        $retVal = false;
        $props = explode('/',$fw->get('PARAMS.0'));
        array_shift($props);
        if ( $props[0] == 'json' ){
			array_shift($props);
		}

        $tadl = new tadl();
        
        unset($props[0]);
        unset($props[1]);

        $namespace = "controllers"; //default namespace
        if (empty($props)) {
            // If it's just a /json call then we want to show all the callable functions.
            // Other wise it will show only the GET functions.
            // since we list, we only want the exposed functions!
            $w = $tadl->get_tadl('exposed');
            $protocol = "ALL";
            $controller = 'tadl';
            $method = 'show';
        } else {
            // but if we have parameters, we want to be able to also access the public ones!
        	$w = $tadl->get_tadl('accessible');
            $controller = array_shift($props) ?: 'tadl';
            $method = array_shift($props) ?: 'show';
        }
        //debug::pe($w[$namespace][$controller]['methods'][$method]);
        //get namespace
        if (!empty($w[$namespace][$controller]['methods'][$method])) {
            $mhd = $w[$namespace][$controller]['methods'][$method];
            if ($protocol != "ALL" && !in_array($protocol, $mhd['protocols'])) {
                $array = array(
                    'code' => 400,
                    'msg' => $protocol . ' protocol is not allowed for that method.',
                );
                return \utils\json::send_json($array);
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
            $result = $c->$method($args);
            if (array_key_exists('data', $result)) {
                $array = array(
                    'data' => !empty($result['data']) ? $result['data'] : false,
                    'code' => !empty($result['code']) ? $result['code'] : 200,
                    'msg' => !empty($result['msg']) ? $result['msg'] : false,
                    'status' => !empty($result['status']) ? $result['status'] : false,
                );
                $data = $array;
            } else {
                $data = array('data' => $result);
            }

            return \utils\json::send_json($data);
        }
        return $retVal;

    }

    function error()
    {
        \utils\json::send_json(404);
    }
}
