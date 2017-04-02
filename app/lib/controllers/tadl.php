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

    public static function load_tadl($directory = null, $namespace = 'controllers\\') {
        $t = new tadl();
        $directory= $directory?:$t->fw->CONTROLLERS;
        $dir = new \DirectoryIterator($directory);
        $retVal = array();
        //$i = new auth();
        foreach ($dir as $fileinfo) {
            $name = $namespace.str_replace(".php", "", $fileinfo->getFilename());
            if (!$fileinfo->isDot()) {
                $class = new $name();
                if (method_exists($class, 'json_register')) {
                    $name::json_register();
                }
            }
        }
    }

    public static function json_register()
    {
        $c = new tadl;
        $c->event->emit('tadl_json_register_start', false);
        //todo maybe \core\controller_model should magically register these for its children?

        self::register('controllers', 'tadl', 'show', array('GET'), 'exposed', 'Send function information to JSON');
        $c->event->emit('tadl_json_register_end', false);
    }

    public function show()
    {
        $this->event->emit('tadl_show_start', false);
        $data = $this->get_tadl('exposed');
        $data = $this->event->emit('tadl_show_end', $data);
        json::send_json(array('data' => $data, 'msg' => 'JSON API Documentation. With great wisdom comes great responsibility'));
    }

    public function get_tadl($scope = 'all')
    {
        $this->event->emit('tadl_'.__FUNCTION__.'_start', false);
        //todo pull from db
        $wadl = $this->fw->exists('TADL') ? $this->fw->get('TADL') : false;
        $wadl = $this->event->emit('tadl_'.__FUNCTION__.'_pull', $wadl);
        if ($wadl && $scope != 'all') {
            $wadl = $wadl[$scope];
        }
        $wadl = $this->event->emit('tadl_'.__FUNCTION__.'_end', $wadl);
        return $wadl;
    }

    /**
     * registers JSON calls with the WADL
     */
    public static function register($namespace, $controller, $method, $protocols = array('GET'), $scope = "public", $comment = '', $args_expected = array())
    {
        $t = new tadl();
        $t->event->emit('tadl_'.__FUNCTION__.'_start', false);
        $wadl = $t->get_tadl();
        $wadl = $t->event->emit('tadl_'.__FUNCTION__.'_pull', $wadl);
        $wadl[$scope][$namespace][$controller]['methods'][$method]['namespace'] = $namespace;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['controller'] = $controller;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['comment'] = $comment;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['args'] = $args_expected;
        $wadl[$scope][$namespace][$controller]['methods'][$method]['protocols'] = $protocols;
        $wadl = $t->event->emit('tadl_'.__FUNCTION__.'_end', $wadl);
        $t->fw->set('TADL', $wadl);
    }
}