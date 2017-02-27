<?php
/**
 * Created by PhpStorm.
 * User: betajo01
 * Date: 9/25/16
 * Time: 7:09 PM
 */

namespace controllers;
use utils\json;

//tiger address listing - list of all functions that can be used in json api

class tadl extends \core\controller_model
{
    public $fw = FALSE;

    public function __construct()
    {
        parent::__construct();
    }

    public function json_register()
    {
        //event_tadl_register_before_submit
        //todo maybe \core\controller_model should magically register these for its children?
        $this->register('controllers', 'tadl', 'show', array('GET'), 'exposed', 'sends wadl to json output');
        $this->register('controllers', 'tadl', 'get_wadl', array('GET'), 'public','gets wadl registrations',
            array(
                array('name' => 'scope',
                    'type' => 'string',
                    'values' => array('exposed', 'public', 'private', 'protected', 'static')
                )
            )
        );
        $this->register('controllers', 'tadl', 'wadl_register', array('GET'), 'public','adds new wadl registrations',
            array(
                array('name' => 'namespace', 'type' => 'string'),
                array('name' => 'controller', 'type' => 'string'),
                array('name' => 'method', 'type' => 'string'),
                array('name' => 'comment', 'type' => 'string'),
                array('name' => 'args_expected', 'type' => 'array'),
                array('name' => 'scope', 'type' => 'string', 'values' => array('exposed', 'public', 'private', 'protected', 'static')),
            )
        );
        //event_wadl_register_return
    }

    public function show()
    {
        //event_wadl_show_before_submit
        $data = $this->get_tadl('exposed');
        //event_wadl_show_data_submit

        json::send_json(200, array('data' => $data, 'msg' => 'JSON API Documentation. With great wisdom comes great responsibility'));
    }

    public function get_tadl($scope = 'all')
    {

        //todo pull from db
        $wadl = $this->fw->exists('WADL') ? $this->fw->get('WADL') : false;
        //event_get_wadl_pull
        if ($wadl && $scope != 'all') {
            $wadl = $wadl[$scope];
        }
        //event_get_wadl_return
        return $wadl;
    }

    /**
     * registers JSON calls with the WADL
     */
    public function register($namespace, $controller, $method, $protocols = array('GET'), $scope = "public", $comment = '', $args_expected = array())
    {
        $wadl = $this->get_tadl();
        // event_wadl_register_pull
        // todo write to db
        $wadl[$scope][$namespace][$controller]['methods'][$method]['namespace'] = $namespace;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['controller'] = $controller;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['comment'] = $comment;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['args'] = $args_expected;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['protocols'] = $protocols;
        $this->fw->set('WADL', $wadl);
        // event_wadl_register_return
    }
}