<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/4/17
 * Time: 1:16 PM
 */


namespace controllers;

use core\controller_model;
use core\tables;

class users extends \core\controller_model
{

    public $user_table_fields = false;

    public static function json_register()
    {
        $user_fields = self::get_user_fields();
        $user_fields['email']['required'] = true;
        $user_fields['password']['required'] = true;
        $user_fields['is_enabled']['default'] = 1;
        $user_fields['user_role_id'] = array('type'=>'int(4)', 'default' => 0 );
        $user_fields['site_id'] = array('type'=>'int(4)', 'required'=>true);
        tadl::register('controllers', 'users', 'create', array('POST'), 'exposed', 'Creates a new user and adds that user to a site',
            array(
                array('userDatum' => 'required', 'type' => $user_fields, 'required'=>true),
            )
        );
        tadl::register('controllers', 'users', 'add_to_site', array('POST'), 'exposed', 'Adds an existing user to a site',
            array(
                array('user_id' => 'required', 'type' => 'int(4)', 'required'=>true),
                array('site_id' => 'required', 'type' => 'int(4)', 'required'=>true),
                array('user_role_id' => 'required', 'type' => 'int(4)', 'default' => 0),
                array('is_enabled' => 'required', 'type' => 'int(4)',  'default' => 1),
            )
        );
        //users_register_end event here
    }

    function add_edit_user_fields(array $fields)
    {
        /*       $fields = = array(
                'table' => 'table_name', //required
                'name' => 'fieldname', //required
                'type' => 'DATETIME', //required
                'nullable' => TRUE,
                'defaults' => '',
                'after' => 'id' //sorts column after another field
                'index' => true //makes this a unique index
                'drop_col' => false //drops this field
                'drop_index' => false // drops this field as an index
                'rename' => 'new name' //renames an existing field
        );
*/
        $fields = $this->event->emit('users_add_fields', $fields);
        return $this->add_table_fields($fields, 'users');
    }

    static function get_user_fields($force_refresh = false)
    {
        $u = new users();
        if($u->user_table_fields == FALSE || $force_refresh) {
            $retVal = $u->event->emit('users_get_fields', $u->get_table_column_fields('users'));
            $u->user_table_fields = $retVal;
        } else {
            $retVal = $u->event->emit('users_get_fields', $u->user_table_fields );
        }
        return $retVal;
    }

    function create () {
        $userDatum = $_POST;
        //error_log($this->fw->stringify($_POST));
        return $this->create_user($userDatum);
    }

    function create_user(array $userDatum)
    {
        //auth todo: check permissions of current admin has access to do this on $site
        /*
        $userDatum = array(
            'user_name' => 'varchar(256)',
            'email' => 'varchar(256)',
            'password' => 'varchar(256)',
            'is_enabled' => 'int(1)',
            'site_id' => 'int(4)',
            'user_role_id' => 'int(4)',
        );
        */
        $site_id = (int) $userDatum['site_id'];
        if(empty($userDatum['password']) || !$site_id || empty($userDatum['email'])) {
            die('Create User Error: A Password, Site ID, and Email must be provided.');
        }

        $user_array = array(
            'table' => 'users',
        );

        $status = array('errors' => false);
        $user = $this->get_data_as_object($user_array); //this is a loaded cortex mapper
        $user_fields = $this->get_user_fields();
        foreach($userDatum as $k => $v) {
            if($k != 'site_id' && $k != 'user_role_id') {
                if(!empty($user_fields[$k])) {
                    $user->$k = $v;
                } else {
                    //todo create a write_log function
                    $status['errors'] = $k. ' non-existent';
                    error_log(__CLASS__ . '::' . __FUNCTION__ . '(Line: ' . __LINE__ . ') - field '.$k.' does not exist on the table. Please create the field before trying to write to it.');
                    continue;
                }
            }
        }
        $user->save();
        $status['user']  = clone($user); //we just want that one variable.
        $user_id = $status['user']->id;

        $user->reset();
        $status['completed'] = 'Success: User '.$user_id.' created';
        $user = false; //PDO method to close the db connection and clear the old user mapper to save memory.
        $status = array_merge($status, $this->add_to_site($user_id,$userDatum['site_id'],$userDatum['user_role_id'],$userDatum['is_enabled'] ));
        return $status;
    }

    function add_to_site($user_id=false, $site_id=false, $user_role_id = 0, $is_enabled = 1)
    {
        $user_id = $user_id?:$_POST['user_id'];
        $site_id = $site_id?:$_POST['site_id'];
        $user_role_id = $user_role_id?:$_POST['user_role_id '];
        $is_enabled = $is_enabled?:$_POST['is_enabled'];

        if(!$user_id || !$site_id) {
            die('Add User To Site: User ID and a Site ID must be provided.');
        }

        $site_array = array(
            'table' => 'site_users',
        );

        $site_user = $this->get_data_as_object($site_array); //this is a loaded cortex mapper
        //error_log($this->fw->stringify($site_user));
        $site_user->user_id = $user_id;
        $site_user->site_id = $site_id;
        $site_user->user_role_id = $user_role_id;
        $site_user->is_enabled = $is_enabled;

        $site_user->save();
        $status['site']  = clone($site_user);
        $site_user->reset();
        $status['completed'] = 'Success: User '.$user_id.' assigned to site ' . $site_id;
        return $status;
    }
    function get_users()
    {

    }

    function get_user()
    {

    }

    function update_user()
    {

    }

    function delete_user()
    {

    }


}