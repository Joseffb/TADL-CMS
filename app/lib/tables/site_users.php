<?php

namespace tables;

use core\db_connect;

class site_users extends \core\tables
{
    protected $table = __CLASS__;
    public function __construct($prefix = FALSE)
    {
        ##### Setup Table Fields #####
        $this->fieldConf['user_id'] = array(
            'type' => 'INT4',
            'nullable' => FALSE,
        );
        $this->fieldConf['site_id'] = array(
            'type' => 'INT4',
            'nullable' => FALSE,
        );
        $this->fieldConf['user_role_id'] = array(
            'type' => 'INT4',
            'nullable' => FALSE,
        );
        $this->fieldConf['is_enabled'] = array(
            'type' => 'INT1',
            'nullable' => FALSE,
        );
        $this->fieldConf = array_merge($this->fieldConf, $this->get_fieldConf());

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

}
