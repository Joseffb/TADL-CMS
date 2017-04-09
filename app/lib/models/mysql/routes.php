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

namespace models\mysql;

use core\controller;

class routes extends \core\model {

    public $fw = null;
    public $admin_theme = null;
    public $site_theme = null;

    public function __construct(  ) {
        parent::__construct();
        $this->fw = \Base::instance();
    }

    public static function lookup_site_by_url ($url = false) {
        //we actually want to get what was requested.
        $site = $url?:$_SERVER['HTTP_HOST'];

        $query = array(
            'table' => "sites",
            'where' => array('url = ? LIMIT 1'),
            'bind_array' =>  array($site),
        );

        // run query mod event here
        // event_login_via_user_password_alter_query
        $a = new controller();
        $response = $a->get_data($query);
        $retVal = false;
        if($response) {
          $retVal = $response;
        }
        return $retVal;
    }

    public static function lookup_site_by_id ($id = false) {
        $query = array(
            'table' => "sites",
            'where' => array('id = ? LIMIT 1'),
            'bind_array' =>  array($id),
        );
        // run query mod event here
        // event_login_via_user_password_alter_query
        $a = new controller();
        $response = $a->get_data($query);
        $retVal = false;
        if($response) {
            $retVal = $response;
        }
        return $retVal;
    }
}