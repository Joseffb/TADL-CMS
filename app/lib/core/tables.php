<?php
/**
 * Created by PhpStorm.
 * User: betajo01
 * Date: 9/25/16
 * Time: 6:00 PM
 *
 * this is the core Cortex Tables file
 */

namespace core;

use tables\audit_log;

class tables extends \DB\Cortex
{
    public $fw = FALSE;
    protected $fluid = false;

    public function __construct()
    {
        $this->fw = \Base::instance();
        parent::__construct();

        $this->beforesave(function ($mapper) {
            if ($this->fw->ENABLE_CHANGE_LOG) {
                if ($this->fw->ENABLE_DETAILED_CHANGE_LOG) {
                    $this->detail_audit_update($mapper);
                } else {
                    $this->basic_audit_update($mapper);
                }
            }
        });
    }

    static public function class_test()
    {
        return TRUE;
    }

    public function basic_audit_update($mapper)
    {
        $c = new controller_model();
        if (!$c->check_if_table_exists('audit_log')) {
            audit_log::setup();
        }
        $audit = new \DB\SQL\Mapper($this->fw->db, 'audit_log');
        $results = $audit->load();
        $results->name = $mapper->table();
        $results->record_id = $mapper->id;
        $results->touch('modified');
        $results->modified_by = $this->fw->USER_ID ?: 0;
        $results->save();
        $results->reset();
    }

    public function detail_audit_update($mapper)
    {
        $c = new controller_model();
        if (!$c->check_if_table_exists('audit_log')) {
            audit_log::setup();
        }
        $audit = new \DB\SQL\Mapper($this->fw->db, 'audit_log');
        $results = $audit->load();
        $results->name = $mapper->table();
        $results->record_id = $mapper->id;
        $results->touch('modified');
        $results->modified_by = $this->fw->USER_ID ?: 0;

        //get list of fields in the table
        $schema = new \DB\SQL\Schema($this->db);
        $fields = $schema->alterTable($mapper->table())->getCols(true);
        foreach ($fields as $fld) {
            if ($mapper->$fld->initial != $mapper->$fld->current) {
                $results->field_name = $fld;
                $results->old_values = $mapper->$fld->initial;
                $results->new_values = $mapper->$fld->current;
            }
        }
        $results->save();
        $results->reset();
    }

    public function get_fieldConf()
    {
        //these fields will be in every table
        return array();
    }
}