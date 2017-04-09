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

class auth extends \core\controller{

    public $fw = null;
    public $admin_theme = null;
    public $site_theme = null;

    public function __construct(  ) {
        parent::__construct();
        $this->fw = \Base::instance();
    }

    public static function lookup_user_authentication ($user_value, $lookup_field_value = "email") {
        $a = new controller();
        $query = array(
            'query_name' => 'lookup_user_authentication',
            'table' => 'users',
            'where' => array($lookup_field_value . '= :value AND  is_enabled = 1'),
            'bind'  => array(":value"=>$user_value)
        );
        // run query mod event here
        // event_login_via_user_password_alter_query

        $response = $a->get_data($query);
        $retVal = false;
        if($response) {
          $retVal = $response;
        }
        return $retVal;
    }
    public static function lookup_api_authentication ($public_key, $lookup_field_value = "public_key") {
        $a = new controller();
        // run query mod event here
        // event_login_via_user_password_alter_query

        $query = array(
            'query_name' => 'lookup_user_authentication',
            'table' => 'users',
            'where' => array($lookup_field_value . '= :value AND is_enabled = 1 AND expire_date > CURRENT_DATE()'),
            'bind'  => array(":value"=>$public_key)
        );
        // run query mod event here
        // event_login_via_user_password_alter_query

        $response = $a->get_data($query);
        $retVal = false;
        if($response) {
            $retVal = $response;
        }
        return $retVal;
    }

}