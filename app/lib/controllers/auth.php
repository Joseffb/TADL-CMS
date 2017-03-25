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

namespace controllers;

class auth extends \core\controller_model
{
    public $authenticated_id = FALSE;
    public $authenticated_type = FALSE;
    public $authorization_level = array(array('realm'=>'public', 'level'=>1));

    public function __construct()
    {
        parent::__construct();
        $this->authenticated_id = $this->authenticated_id ?: !$this->fw->DEVOID('SESSION.authenticated_id')?$this->fw->GET('SESSION.authenticated_id'):false;
        $this->authenticated_type = $this->authenticated_type ?: !$this->fw->DEVOID('SESSION.authenticated_type')?$this->fw->GET('SESSION.authenticated_type'):false;
        $this->authorization_level = $this->authorization_level ?: !$this->fw->DEVOID('SESSION.authorization_level')?$this->fw->GET('SESSION.authorization_level'):$this->authorization_level;

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

    public function is_logged_in() {
        return $this->check_authentication();
    }

    public function check_authentication()
    {
        $retVal = false;
        if($this->authenticated_id) {
            $retVal = true;
        }
        return $retVal;
    }

    public function check_authorization($realm = "public", $level = 1)
    {
        $retVal = false;
        // run event_check_authorization_submit
        if(!empty($this->$authorization_level)) {
            foreach($this->$authorization_level as $allowance) {
                if($allowance['realm'] == $realm && $allowance['level'] >= $level) {
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
        $class = $this->get_model_path(__CLASS__);
        //todo mongo and jig versions.
        $response = $class::lookup_user_authentication($user_value, $field_value);
        if ( $response ) {
            if($retVal = password_verify($password_value, $response[0]->password)) { // returns true/false
                //set session vars here.
                $this->authenticated_id = $response[0]->id;
                $this->authorization_level  = $this->get_authorization_by_user_id($response[0]->id);
                $this->authenticated_type = "user";
                $this->fw->SET('USER_ID', "USER-".$this->authenticated_id);
                $this->fw->SET('SESSION.authenticated_id', $this->authenticated_id);
                $this->fw->SET('SESSION.authenticated_type', $this->authenticated_type);
                $this->fw->SET('SESSION.authorization_level', $this->authorization_level);
                $retVal = $response[0];
            }
        }
        // event_login_via_user_password_alter_result
        return $retVal;
    }

    public function login_via_api_key($public_key, $hash, $create_time)
    {
        $retVal = false;
        $php_timeout = (int) ini_get("max_execution_time");
        if($create_time > time()-$php_timeout || $create_time < time()+$php_timeout) {
            //make sure that the request is somewhat recent. We use the system max time to ensure the call is from same session.
            return $retVal;
        }

        $class = $this->get_model_path(__CLASS__);
        $response = $class::lookup_api_authentication($public_key);

        if ( !empty($response) ) {
            //todo - figure this out with real crypto solution
                if( md5($create_time . $response[0]->private_key) == $hash) {
                    //set session vars here.
                    $this->authenticated_id = $response[0]->id;
                    $this->authorization_level  = $this->get_authorization_by_key_id($response[0]->id);
                    $this->authenticated_type = "api";
                    $this->fw->SET('USER_ID', "APIKEY-".$this->authenticated_id);
                    $this->fw->SET('SESSION.authenticated_id', $this->authenticated_id);
                    $this->fw->SET('SESSION.authenticated_type', $this->authenticated_type);
                    $this->fw->SET('SESSION.authorization_level', $this->authorization_level);
                    $retVal = $response[0];
                };
        }
        // event_login_via_api_key_alter_result
        return $retVal;
    }

    function logout($login = "/", $send_headers = true) {
        // event_logout_before_submit
        $this->authenticated_id =  $this->authenticated_type = FALSE;
        $this->authorization_level  = array(array('realm'=>'public', 'level'=>1));
        $this->fw->SET('USER_ID', $this->authenticated_id);
        $this->fw->SET('SESSION.authenticated_id', $this->authenticated_id);
        $this->fw->SET('SESSION.authenticated_type', $this->authenticated_type);
        $this->fw->SET('SESSION.authorization_level', $this->authorization_level);
        // $retVal = event_logout_alter_result
        if($send_headers) {
            header( 'WWW-Authenticate: Basic realm="'.$this->fw->SITENAME?:"Tiger Site".'"' );
            header( 'HTTP/1.0 401 Unauthorized' );
            echo "<script>window.location = '".$login."';</script>";
        }
        return true;
    }
}