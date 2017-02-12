<?php

namespace tables;

use core\db_connect;

class users extends \core\tables
{
    protected $table = 'users';
    public function __construct($prefix = FALSE)
    {
        $this->fieldConf = $this->get_fieldConf();
        ##### Setup Table Fields #####
        $this->fieldConf['user_name'] = array(
            'type' => 'VARCHAR256',
            'nullable' => FALSE,
        );
        $this->fieldConf['email'] = array(
            'type' => 'VARCHAR256',
            'nullable' => FALSE,
        );
        $this->fieldConf['password'] = array(
            'type' => 'VARCHAR256',
            'nullable' => FALSE,
        );
        $this->fieldConf['is_enabled'] = array(
            'type' => 'INT1',
            'nullable' => FALSE,
        );

        $this->fw = \Base::instance();

        //Use this way if you want a secondary database;
        //sets all db's up from config, and returns default
        //$this->db = $this->fw->exists('DB2') ?$this->fw->get('DB2'): core_DBC::dbc(2);

        $this->db = $this->fw->exists('DB') ?$this->fw->get('DB'): db_connect::boot();
        parent::__construct();

    }

    public static function setup($db = NULL, $table = NULL, $fields = NULL)
    {
        parent::setup($db, $table, $fields);

        //Check if the table has default values -- good for importing
        $class = get_class();
        if (method_exists($class, 'default_values')
            && is_callable(array($class, 'default_values'))
        ) {
            $table = new $class();
            $table->default_values($table);
        }
    }

    // check for proper email before saving
    public function set_email($value)
    {
        if (\Audit::instance()->email($value) == FALSE) {
            // no valid email address
            error_log($value . "is not a proper email address and was not saved to the users table. ERROR 402");
            return false;
        }

        return $value;
    }

    // encrypt a password before saving
    public function set_password($value)
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    //Note: These default uses need to remain in the same order so that their primary key IDs get created in the expected order.
    //	These user IDs are used on the Global_Employers table load_default_values().
    //  Summary: Don't mess with the order of these array entries or you'll probably screw up the employer info preloading.
    public function default_values($table)
    {

        $VALUES = array(
            array(
                'admin',
                'admin@nowhere.com',
                'password',
                1
            ),
        );

        foreach ($VALUES as $v) {
            $table->user_name = $v[0];
            $table->email = $v[1];
            $table->password = $v[2];
            $table->is_enabled = $v[3];
            $table->save();
            $table->reset();

            //todo add user to security group in the security group table.
        }
    }
}
