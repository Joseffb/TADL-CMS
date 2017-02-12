<?php
/**
 * Created by PhpStorm.
 * User: betajo01
 * Date: 9/25/16
 * Time: 7:09 PM
 */

namespace controllers;

use utils\json;

class wadl extends \core\controller_model
{
    public $fw = FALSE;

    public function __construct()
    {
        parent::__construct();
    }

    public function register()
    {
        //event_wadl_register_before_submit
        //todo maybe \core\controller_model should magically register these for its children?
        $this->wadl_register('controllers', 'wadl', 'show', array('GET'), 'exposed', 'sends wadl to json output');
        $this->wadl_register('controllers', 'wadl', 'get_wadl', array('GET'), 'public','gets wadl registrations',
            array(
                array('name' => 'scope',
                    'type' => 'string',
                    'values' => array('exposed', 'public', 'private', 'protected', 'static')
                )
            )
        );
        $this->wadl_register('controllers', 'wadl', 'wadl_register', array('GET'), 'public','adds new wadl registrations',
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
        $data = $this->get_wadl('exposed');
        //event_wadl_show_data_submit
        json::send_json(200, array('data' => $data, 'msg' => 'With great wisdom comes great responsibility'));
    }

    public function get_wadl($scope = 'all')
    {

        $wadl = $this->fw->exists('WADL') ? $this->fw->get('WADL') : false;
        //event_get_wadl_pull
        if ($wadl && $scope != 'all') {
            $wadl = $wadl[$scope];
        }

        //todo add in ability to hide parts of wadl like namespace
        //event_get_wadl_return
        return $wadl;
    }

    /**
     * registers JSON calls with the WADL
     */
    public function wadl_register($namespace, $controller, $method, $protocols = array('GET'), $scope = "public", $comment = '', $args_expected = array())
    {
        $wadl = $this->get_wadl();
        //event_wadl_register_pull
        $wadl[$scope][$namespace][$controller]['methods'][$method]['namespace'] = $namespace;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['controller'] = $controller;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['comment'] = $comment;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['args'] = $args_expected;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['protocols'] = $protocols;
        $this->fw->set('WADL', $wadl);
        //event_wadl_register_return
    }
}