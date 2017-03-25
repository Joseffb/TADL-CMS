<?php

namespace tables;

use core\db_connect;

class sites extends \core\tables
{
    protected $table = __CLASS__;
    public function __construct()
    {
        $this->fw = \Base::instance();
        ##### Setup Table Fields #####
        $this->fieldConf['url'] = array(
            'type' => 'VARCHAR256',
            'nullable' => FALSE,
        );
        $this->fieldConf['name'] = array(
            'type' => 'VARCHAR256',
            'nullable' => FALSE,
        );
        $this->fieldConf['from_email'] = array(
            'type' => 'VARCHAR256',
            'nullable' => FALSE,
        );
        $this->fieldConf['admin_email'] = array(
            'type' => 'VARCHAR256',
            'nullable' => FALSE,
            'default' => !$this->fw->devoid('SITE_SYSTEM_EMAIL')?$this->fw->SITE_SYSTEM_EMAIL:'',
        );
        $this->fieldConf['theme'] = array(
            'type' => 'VARCHAR256',
            'nullable' => FALSE,
            'default' => 'default',
        );
        $this->fieldConf['is_enabled'] = array(
            'type' => 'INT1',
            'nullable' => FALSE,
            'default' => 1,
        );
        $this->fieldConf['created'] = array(
            'type' => 'TIMESTAMP',
            'nullable' => false,
            'default' => 'CUR_STAMP',
        );

        $this->fieldConf = array_merge($this->fieldConf, $this->get_fieldConf());


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
    public function set_system_email($value)
    {
        if (\Audit::instance()->email($value) == FALSE) {
            // no valid email address
            //todo replace with language dictionary entries
            error_log($value . "is not a proper system email address and was not saved to the users table. ERROR 402");
            return false;
        }

        return $value;
    }
}
