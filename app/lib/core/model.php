<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 4/5/17
 * Time: 11:29 AM
 */

namespace core;


class model extends \Prefab {
    public $fw, $db;
    protected $queries = array();
    public $event = false;

    function __construct() {
        $DB = false;
        $this->fw = \Base::instance();
        $this->connect_db();
        $this->event = new \Event();
    }

    public function connect_db()
    {
        if ($this->fw->devoid('DB')) {
            new db_connect();
        }
        $this->db = $this->fw->GET('DB');
    }

    public function get_parameters()
    {
        $options = false;
        $num_args = func_num_args();
        if ($num_args == 1 && is_array(func_get_arg(0))) {
            //$options array was passed in complete as sole argument.
            $options = func_get_arg(0);
        } elseif ($num_args > 1) {
            //individual arguments passed in.
            $arg_list = func_get_args();
            $options = array();
            for ($i = 0; $i < $num_args; $i++) {
                $a = explode("|", $arg_list[$i]); //using pipe as separator because bindings and queries may use > = <
                $options[trim($a[0])] = trim($a[1]);
                if (strpos($a[1], 'array(')) {
                    // someone passed  'name | array(key => value)';
                    // we will convert it using json functions instead of evil eval.
                    $options[$a[0]] = json_decode(json_encode($a[1]));
                }
                $a = null;
            }
        }
        return $options;
    }

    public function check_if_table_exists($table, $DB = false)
    {
        $DB = $DB ?: $this->db;
        if (!$DB) {
            return false;
        }
        $retVal = FALSE;
        $schema = new \DB\SQL\Schema($DB);
        if (in_array($table, $schema->getTables())) {
            $retVal = TRUE;
        }

        return $retVal;
    }

    public function check_if_fields_exists_in_table($table, array $fields, $DB = false)
    {
        $DB = $DB ?: $this->db;
        if (!$DB) {
            return false;
        }
        $retVal = false;
        $fieldsProps = $DB->schema($table, implode(";", $fields));
        if ($fieldsProps) {
            $retVal = $fieldsProps;
        }
        return $retVal;
    }

    public function update_each_field_type($tbl_name, $check_only = false, $DB = false)
    {
        $DB = $DB ?: $this->db;
        if (!$DB) {
            return false;
        }
        $retVal = false;
        $schema = new \DB\SQL\Schema($DB);
        $child = get_class("\\tables\\" . $tbl_name);
        $table_name = $child->$table;
        $cortex_field_definitions = $child->$fieldConf;
        $tableInfo = $schema->alterTable($table_name)->getCols(true);
        foreach ($cortex_field_definitions as $columnName => $fieldConf) {
            $condition = $tableInfo[$columnName] == $fieldConf['type'];
            $condition = $condition + $schema->isCompatible($fieldConf['type'], $columnName);
            if ($check_only) {
                $retVal = $condition;
            } else if ($condition) {
                $tableInfo->updateColumn($columnName, $fieldConf['type'], true);
                $tableInfo->build();
                $retVal = true;
            }
        }
        return $retVal;
    }

    function add_table_fields(array $fields, $table_override = false)
    {
        $s = new \DB\SQL\Schema($this->db);
        /*        $fields = = array(
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
        foreach ($fields as $field) {
            $table = $s->alterTable($field['table']);
            if (!empty($field['drop_col'])) {
                $table->dropColumn($field['drop_col']);
            } else if (!empty($field['drop_index'])) {
                $table->dropIndex($field['drop_col']);
            } else if (!empty($field['rename'])) {
                $table->renameColumn($field['name'], $field['rename']);
            } else {
                $array = array();
                if (!empty($field['table'])) {
                    $array['table'] = $field['table'];
                }
                if (!empty($table_override)) {
                    $array['table'] = $table_override;
                }
                if (!empty($field['name'])) {
                    $array['name'] = $field['name'];
                }
                if (!empty($field['type'])) {
                    $array['type'] = $field['type'];
                }

                if (empty($array)) {
                    //todo create a write_log function
                    error_log(__CLASS__ . '::' . __FUNCTION__ . '(Line: ' . __LINE__ . ') - missing required field attributes name and/or type');
                    continue; //missing required fields, can't add this field so we goto next field.
                }

                if (!empty($field['nullable'])) {
                    $array['nullable'] = $field['nullable'];
                }
                if (!empty($field['defaults'])) {
                    $array['defaults'] = $field['defaults'];
                }
                if (!empty($field['after'])) {
                    $array['after'] = $field['after'];
                }
                if (!empty($field['index'])) {
                    $array['index'] = $field['index'];
                }

            }

            $table->build();
        }
        return true;
    }

    function get_table_column_fields($table) {
        $s = new \DB\SQL\Schema($this->db);
        $tbl = $s->alterTable($table);
        return $tbl->getCols(true);
    }
    public function escape($value)
    {
        if ($value) {
            return "`" . str_replace("`", "``", $value) . "`";
        } else {
            error_log("Cannot escape " . $value . ". Operation aborted for the greater good of all mankind. 500");
            die();
        }
    }
}