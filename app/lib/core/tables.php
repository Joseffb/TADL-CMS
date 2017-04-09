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

use App\Schema;
use tables\audit_log;

class tables extends \DB\Cortex
{
    public $fw = FALSE;
    protected $fluid = false;
    protected $event = false;

    public function __construct()
    {
        $this->fw = \Base::instance();
        parent::__construct();
        $this->event = new \Event();
        $this->beforesave(function ($mapper) {
            $this->event->emit('tables_beforesave_start', false);
            if ($this->fw->ENABLE_CHANGE_LOG) {
                if ($mapper->table() != 'audit_log') {
                    //Can't audit the audit log, will cause infinite loop.
                    if ($this->fw->ENABLE_DETAILED_CHANGE_LOG) {
                        $this->detail_audit_update($mapper);
                    } else {
                        $this->basic_audit_update($mapper);
                    }
                }
            }
            $this->event->emit('tables_beforesave_end', false);
        });
    }

    static public function class_test()
    {
        return TRUE;
    }

    public function basic_audit_update($mapper)
    {
        $this->event->emit('tables_basic_audit_update_start', false);
        $c = new controller();
        if (!$c->check_if_table_exists('audit_log')) {
            audit_log::setup();
        }
        $c = new controller();
        $query = array(
            'query_name' => 'basic_audit_update',
            'table' => 'audit_log',
            'method' => 'load'
        );
        $results = $c->get_data($query);
        $results->name = $mapper->table();
        $results->record_id = $mapper->id;
        $results->touch('modified');
        $results->modified_by = $this->fw->USER_ID ?: 0;
        $results = $this->event->emit('tables_basic_audit_update_save', $results);
        $results->save();
        $results->reset();
        $this->event->emit('tables_basic_audit_update_end', false);
    }

    public function detail_audit_update($mapper)
    {

        $this->event->emit('tables_detail_audit_update_start', false);
        $c = new controller();
        if (!$c->check_if_table_exists('audit_log')) {
            audit_log::setup();
        }
        $c = new controller();
        $query = array(
            'query_name' => 'detail_audit_update',
            'table' => 'audit_log',
            'method' => 'load'
        );

        $results = $c->get_data($query);
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

        $results = $this->event->emit('tables_detail_audit_update_save', $results);
        $results->save();
        $results->reset();
        $this->event->emit('tables_detail_audit_update_end', false);
    }


    public function get_fieldConf()
    {
        //these fields will be in every table
        return array();
    }
}