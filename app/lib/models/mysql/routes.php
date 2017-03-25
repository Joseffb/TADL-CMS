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

class routes extends \core\controller_model {

    public $fw = null;
    public $admin_theme = null;
    public $site_theme = null;

    public function __construct(  ) {
        parent::__construct();
        $this->fw = \Base::instance();
    }

    public static function lookup_site_id ($url = false) {
        //we actually want to get what was requested.
        $site = $url?:$_SERVER['HTTP_HOST'];
        $query = array(
            'type' => "sql",
            'query' => "SELECT * FROM sites WHERE site_URL = ? LIMIT 1", //should only bring back one result per site
            'bind_array' =>  array($site),
        );
        // run query mod event here
        // event_login_via_user_password_alter_query
        $a = new controller_model();
        $response = $a->get_data_as_object($query);
        $retVal = false;
        if($response) {
          $retVal = $response;
        }
        return $retVal;
    }


}