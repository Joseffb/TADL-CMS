<?php
/**
 * Created by PhpStorm.
 * User: betajo01
 * Date: 9/25/16
 * Time: 7:09 PM
 */

namespace controllers;
use utils\json;
use utils\debug;
//tiger address listing - list of all functions that can be used in json api

class tadl extends \core\controller
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
        $tadl = $this->fw->exists('TADL') ? $this->fw->get('TADL') : false;
        $tadl = $this->event->emit('tadl_'.__FUNCTION__.'_pull', $tadl);

        if ($tadl && $scope != 'all') {
            $tadl = $tadl[$scope];
        }
        $tadl = $this->event->emit('tadl_'.__FUNCTION__.'_end', $tadl);
        //debug::pe($tadl);
        return $tadl;
    }

    /**
     * registers JSON calls with the WADL
     */
    public static function register($namespace, $controller, $method, $protocols = array('GET'), $scope = "public", $comment = '', $args_expected = array())
    {
        $t = new tadl();
        $t->event->emit('tadl_'.__FUNCTION__.'_start', false);
        $tadl = $t->get_tadl();
        $tadl = $t->event->emit('tadl_'.__FUNCTION__.'_pull', $tadl);
        $tadl[$scope][$namespace][$controller]['methods'][$method]['namespace'] = $namespace;
        $tadl[$scope][$namespace][$controller]['methods'][$method]['controller'] = $controller;
        $tadl[$scope][$namespace][$controller]['methods'][$method]['comment'] = $comment;
        $tadl[$scope][$namespace][$controller]['methods'][$method]['args'] = $args_expected;
        $tadl[$scope][$namespace][$controller]['methods'][$method]['protocols'] = $protocols;
        $tadl = $t->event->emit('tadl_'.__FUNCTION__.'_end', $tadl);
        $t->fw->set('TADL', $tadl);
    }
}