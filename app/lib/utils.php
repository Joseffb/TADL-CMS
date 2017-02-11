<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 10:26 AM
 */

namespace lib;


class utils {
    static function pe($stuff) {
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

    static function void($key) {
        $fw = \Base::instance();
        $r = false;
        if(!$fw->exists($key)) {
            $r = true;
        } elseif($fw->devoid($key)) {
            $r = true;
        } elseif($fw->exists($key) && $fw->devoid($key)) {
            $r = true;
        } elseif ($fw->exists($key) && !$fw->devoid($key)) {
            $r = false;
        }
        return $r;
    }

    static function not_void($key) {
        return !self::void($key);
    }

    static function send_json($code, $options = null) {
        //self::pe($options);
        if(!empty($options) && !is_array($options)) {
            $data = $options;
            $msg = null;
            $status = null;

        } else {
            $data = !empty($options['data']) ?$options['data']: null;
            $msg = !empty($options['msg']) ?$options['msg']: null;
            $status = !empty($options['status']) ?$options['status']: null;
        }
        $json_message = self::get_json($code, $data, $msg, $status);
        header('Content-Type: application/json', true, $code);
        echo json_encode($json_message);
        die();
    }

    static function get_json($code, $data = null, $msg = false , $status = 'OK') {
        //$fw = \Base::instance();
        $http_codes = array(
            100 => array('status'=>'OK','msg'=>'Continue'),
            101 => array('status'=>'OK','msg'=>'Switching Protocols'),
            102 => array('status'=>'OK','msg'=>'Processing'),
            200 => array('status'=>'OK','msg'=>'OK'),
            201 => array('status'=>'OK','msg'=>'Created'),
            202 => array('status'=>'OK','msg'=>'Accepted'),
            203 => array('status'=>'ERR','msg'=>'Non-Authoritative Information'),
            204 => array('status'=>'ERR','msg'=>'No Content'),
            205 => array('status'=>'ERR','msg'=>'Reset Content'),
            206 => array('status'=>'ERR','msg'=>'Partial Content'),
            207 => array('status'=>'ERR','msg'=>'Multi-Status'),
            300 => array('status'=>'ERR','msg'=>'Multiple Choices'),
            301 => array('status'=>'ERR','msg'=>'Moved Permanently'),
            302 => array('status'=>'ERR','msg'=>'Found'),
            303 => array('status'=>'ERR','msg'=>'See Other'),
            304 => array('status'=>'ERR','msg'=>'Not Modified'),
            305 => array('status'=>'ERR','msg'=>'Use Proxy'),
            306 => array('status'=>'ERR','msg'=>'Switch Proxy'),
            307 => array('status'=>'ERR','msg'=>'Temporary Redirect'),
            400 => array('status'=>'ERR','msg'=>'Bad Request'),
            401 => array('status'=>'ERR','msg'=>'Unauthorized'),
            402 => array('status'=>'ERR','msg'=>'Payment Required'),
            403 => array('status'=>'ERR','msg'=>'Forbidden'),
            404 => array('status'=>'ERR','msg'=>'Not Found'),
            405 => array('status'=>'ERR','msg'=>'Method Not Allowed'),
            406 => array('status'=>'ERR','msg'=>'Not Acceptable'),
            407 => array('status'=>'ERR','msg'=>'Proxy Authentication Required'),
            408 => array('status'=>'ERR','msg'=>'Request Timeout'),
            409 => array('status'=>'ERR','msg'=>'Conflict'),
            410 => array('status'=>'ERR','msg'=>'Gone'),
            411 => array('status'=>'ERR','msg'=>'Length Required'),
            412 => array('status'=>'ERR','msg'=>'Precondition Failed'),
            413 => array('status'=>'ERR','msg'=>'Request Entity Too Large'),
            414 => array('status'=>'ERR','msg'=>'Request-URI Too Long'),
            415 => array('status'=>'ERR','msg'=>'Unsupported Media Type'),
            416 => array('status'=>'ERR','msg'=>'Requested Range Not Satisfiable'),
            417 => array('status'=>'ERR','msg'=>'Expectation Failed'),
            418 => array('status'=>'ERR','msg'=>'I\'m a teapot'),
            422 => array('status'=>'ERR','msg'=>'Unprocessable Entity'),
            423 => array('status'=>'ERR','msg'=>'Locked'),
            424 => array('status'=>'ERR','msg'=>'Failed Dependency'),
            425 => array('status'=>'ERR','msg'=>'Unordered Collection'),
            426 => array('status'=>'ERR','msg'=>'Upgrade Required'),
            449 => array('status'=>'ERR','msg'=>'Retry With'),
            450 => array('status'=>'ERR','msg'=>'Blocked by Windows Parental Controls'),
            500 => array('status'=>'ERR','msg'=>'Internal Server Error'),
            501 => array('status'=>'ERR','msg'=>'Not Implemented'),
            502 => array('status'=>'ERR','msg'=>'Bad Gateway'),
            503 => array('status'=>'ERR','msg'=>'Service Unavailable'),
            504 => array('status'=>'ERR','msg'=>'Gateway Timeout'),
            505 => array('status'=>'ERR','msg'=>'HTTP Version Not Supported'),
            506 => array('status'=>'ERR','msg'=>'Variant Also Negotiates'),
            507 => array('status'=>'ERR','msg'=>'Insufficient Storage'),
            509 => array('status'=>'ERR','msg'=>'Bandwidth Limit Exceeded'),
            510 => array('status'=>'ERR','msg'=>'Not Extended')
        );

        $json_message = array(
            'code' => $code,
            'status' => !empty($status)?$status:$http_codes[$code]['status'],
            'msg' => !empty($msg)?$msg:$http_codes[$code]['msg']
        );
        if($data) {
            $json_message['data'] = $data;
        }
        return $json_message;
    }

    function get_params($int = false) {
        $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri_segments = explode('/', $uri_path);
        if ($int && is_int($int)) {
            $retVal = $uri_segments[$int];
        } else {
            $retVal = $uri_segments;
        }
        return $retVal;
    }
}
