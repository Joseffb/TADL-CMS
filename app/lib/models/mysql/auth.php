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

use core\controller_model;

class auth extends \core\controller_model {

    public $fw = null;
    public $admin_theme = null;
    public $site_theme = null;

    public function __construct(  ) {
        parent::__construct();
        $this->fw = \Base::instance();
    }

    public static function lookup_user_authentication ($user_value, $lookup_field_value = "email") {
        $a = new controller_model();
        $site = $site?:$a->SITE_ID;
        $where = " WHERE (" . mysql_real_escape_string($lookup_field_value) . " = :value and is_enabled = 1";
        $query = array(
            'type' => "sql",
            'query' => "SELECT * FROM users " . $where,
            'bind_array' =>  array(":value"=>$user_value),
        );
        // run query mod event here
        // event_login_via_user_password_alter_query

        $response = $a->get_data_as_object($query);
        $retVal = false;
        if($response) {
          $retVal = $response;
        }
        return $retVal;
    }
    public static function lookup_api_authentication ($public_key, $lookup_field_value = "public_key") {
        $a = new controller_model();
        $site = $site?:$a->SITE_ID;
        $where = " WHERE (" . mysql_real_escape_string($lookup_field_value) . " = :value and is_enabled = 1 and expire_date > CURRENT_DATE() ";
        $query = array(
            'type' => "sql",
            'query' => "SELECT * FROM api_keys " . $where,
            'bind_array' =>  array(":value"=>$public_key),
        );
        // run query mod event here
        // event_login_via_user_password_alter_query

        $response = $a->get_data_as_object($query);
        $retVal = false;
        if($response) {
            $retVal = $response;
        }
        return $retVal;
    }

}