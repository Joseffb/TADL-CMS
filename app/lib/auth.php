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

namespace lib;

class auth_ctl extends core_cm
{
    public $authenticated_id = FALSE;
    public $authenticated_type = FALSE;
    public $authorization_level = FALSE;

    public function __construct()
    {
        parent::__construct();
        $this->authenticated_id = !$this->fw->DEVOID('SESSION.authenticated_id')?$this->fw->GET('SESSION.authenticated_id'):$this->authenticated_id;
        $this->authenticated_type = !$this->fw->DEVOID('SESSION.authenticated_type')?$this->fw->GET('SESSION.authenticated_type'):$this->authenticated_type;
        $this->authorization_level = !$this->fw->DEVOID('SESSION.authorization_level')?$this->fw->GET('SESSION.authorization_level'):$this->authorization_level;

    }

    public function get_authorization_rules()
    {

    }

    public function get_authorization_levels()
    {

    }

    public function get_authorization_by_user_id()
    {

    }
    public function get_authorization_by_key_id()
    {

    }
    public function check_authentication()
    {
        $retVal = false;
        if($this->authenticated_id) {
            $retVal = true;
        }
        return $retVal;
    }

    public function check_authorization($realm, $level)
    {
        $retVal = false;
        // run event_check_authorization_submit
        if(!empty($this->$authorization_level)) {
            foreach($this->$authorization_level as $allowance) {
                if($allowance['realm'] == $realm && $allowance['realm']['level'] >= $level) {
                    //$retVal = event_check_authorization_authorization_found
                    $retVal = true;
                    break;
                }
            }
        }
        // run event_check_authorization_alter_result
        return $retVal;
    }

    public function login(array $options) {
        // run event_login_submit
        if(!empty($options['api_key'])) {
            $retVal =  $this->login_via_api_key($options['api_key'],$options['hash'],$options['create_time']);
        } else {
            $retVal =  $this->login_via_user_password($options['user'], $options['password'],$options['field_value']);
        }
        // run event_login_alter_result
        return $retVal;


    }

    public function login_via_user_password($user_value, $password_value = false, $field_value = "email")
    {
        $retVal = false;
        $USER_TABLE = !$this->fw->DEVOID('DB_USERTABLE') ?$this->fw->GET('DB_USERTABLE'): false;
        $USER_TABLE = $USER_TABLE && $this->check_if_table_exists($USER_TABLE) ? $USER_TABLE : 'users';

        $field_value = $this->escape($field_value);
        $where = " WHERE (" .$field_value . " = :value and is_enabled = 1";
        $query = array(
                'type' => "sql",
                'query' => "SELECT * FROM " . $USER_TABLE . $where,
                'bind_array' =>  array(":value"=>$user_value),
        );
        // run query mod event here
        // event_login_via_user_password_alter_query

        $response = $this->get_data_as_object($query);
        if ( !empty($response) ) {
            if($retVal = password_verify($password_value, $response[0]['password'])) { // returns true/false
                //set session vars here.
                $this->authenticated_id = $response[0]['id'];
                $this->authorization_levels  = $this->get_authorization_by_user_id($response[0]['id']);
                $this->authenticated_type = "user";
                $this->fw->SET('USER_ID', "USER-".$this->authenticated_id);
                $this->fw->SET('SESSION.authenticated_id', $this->authenticated_id);
                $this->fw->SET('SESSION.authenticated_type', $this->authenticated_type);
                $this->fw->SET('SESSION.authorization_level', $this->authorization_levels);
                $retVal = $response[0];
            }
        }
        // event_login_via_user_password_alter_result
        return $retVal;
    }

    public function login_via_api_key($public_key, $hash, $create_time)
    {
        $retVal = false;

        $TABLE = !$this->fw->DEVOID('DB_APIKEYS') ?$this->fw->GET('DB_APIKEYS'): false;
        $TABLE = $TABLE && $this->check_if_table_exists($TABLE) ? $TABLE : 'users';

        $field_value = 'api_key';
        $where = " WHERE (" .$field_value . " = :value and is_enabled = 1";

        $query = array(
            'type' => "sql",
            'query' => "SELECT * FROM " . $TABLE . $where,
            'bind_array' =>  array(":value"=>$public_key),
        );
        // event_login_via_api_key_alter_query

        $response = $this->get_data_as_object($query);
        if ( !empty($response) ) {
            //todo - figure this out with real crypto solution
                if( md5($create_time . $response[0]['private_key']) == $hash) {
                    //set session vars here.
                    $this->authenticated_id = $response[0]['id'];
                    $this->authorization_levels  = $this->get_authorization_by_key_id($response[0]['id']);
                    $this->authenticated_type = "api";
                    $this->fw->SET('USER_ID', "APIKEY-".$this->authenticated_id);
                    $this->fw->SET('SESSION.authenticated_id', $this->authenticated_id);
                    $this->fw->SET('SESSION.authenticated_type', $this->authenticated_type);
                    $this->fw->SET('SESSION.authorization_level', $this->authorization_levels);
                    $retVal = $response[0];
                };
        }
        // event_login_via_api_key_alter_result
        return $retVal;
    }

    function logout() {
        // event_logout_before_submit
        $retVal = false;
        $this->authenticated_id = $this->authorization_levels  = $this->authenticated_type = FALSE;
        $send_headers = true;
        // $retVal = event_logout_alter_result
        $retVal = true;
        if($send_headers) {
            header( 'WWW-Authenticate: Basic realm="'.$this->fw->SITENAME?:"Tiger Site".'"' );
            header( 'HTTP/1.0 401 Unauthorized' );
            echo "<script>window.location = '".$login."';</script>";
        }
        return $retVal;

    }
}