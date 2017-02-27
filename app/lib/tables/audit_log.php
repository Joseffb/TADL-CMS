<?php

namespace tables;

use core\db_connect;

class audit_log extends \core\tables
{
    protected $table = 'audit_log';

    public function __construct($prefix = FALSE)
    {
        $this->fieldConf = array(
            'name' => array(
                'type' => 'VARCHAR256',
                'nullable' => false,
            ),
            'record_id' => array(
                'type' => 'INT4',
                'nullable' => false,
            ),
            'modified_by' => array(
                'type' => 'VARCHAR256',
                'nullable' => false,
            ),
            'field_name' => array(
                'type' => 'VARCHAR256',
                'nullable' => false,
            ),
            'old_value' => array(
                'type' => 'VARCHAR256',
                'nullable' => false,
            ),
            'new_value' => array(
                'type' => 'VARCHAR256',
                'nullable' => false,
            ),
            'modified' => array(
                'type' => 'TIMESTAMP',
                'nullable' => false,
            ),
            'created' => array(
                'type' => 'TIMESTAMP',
                'nullable' => false,
                'default' => 'CUR_STAMP',
            ),
        );
        $this->fw = \Base::instance();

        //Use this way if you want a secondary database;
        //sets all db's up from config, and returns default
        //$this->db = $this->fw->exists('DB2') ?$this->fw->get('DB2'): core_DBC::dbc(2);

        $this->db = $this->fw->exists('DB') ? $this->fw->get('DB') : db_connect::boot();
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
