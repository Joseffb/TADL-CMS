<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/1/17
 * Time: 10:56 PM
 *
 * This is hte core controller / model file.
 */

namespace core;


class controller_model extends \Prefab
{

    public $fw, $db;
    protected $queries = array();

    public function __construct()
    {
        $DB = false;
        $this->fw = \Base::instance();
        $this->connect_db();
    }

    public function connect_db()
    {
        if ($this->fw->devoid('DB')) {
            new db_connect();
        }
        $this->db = $this->fw->GET('DB');
    }

    public function test () {
        echo "test";
    }


    public function get_model_path($class_name, $namespace = false, $db_type = false) {
        if($namespace) {
            $namespace = $namespace."\\";
            $class_name = str_replace($namespace, '',$class_name);
        }
        $db_type=$db_type?:$this->fw->DB_TYPE;
        $is_sql = $db_type != "jig"  &&  $db_type != "mongo";
        $path = "\\models\\".$db_type."\\".$class_name;
        if($is_sql && $db_type != 'mysql') {
            if(!class_exists($path)) {
                //allow other sql to default to the mysql variant if there are missing routes for that sql variant.
                $path = "\\models\\mysql\\".$class_name;
            }
        }
        return $path;
    }

    public function get_data_as_object(array $options)
    {
        /*
         * $options = array (
         *  'DB' => DB ID
         *  'type' => cortex | mapper | sql | mongo | jig
         *  'query' => based on type choice above
         *  'table' => used with cortex or mapper
         *  'bind_array' => array("?" => 'value' ) - used in sql queries.
         *  'pagination' => array('start' => int, length   => int)
         * )
         */
        $retVal = false;
        if (!empty($options['table']) && !$this->check_if_table_exists($options['table'])) {
            //security check -- make sure the table is in the DB we are have configured and not in the system db or elsewhere.
            return $retVal;
        }
        $DB = !empty($options['DB']) ? $this->fw->get($options['DB']) : $this->db;

        switch ($options['type']) {
            // Use this for reading and pulling data from the db.
            case 'sql':
                if(empty($options['query_name'])) {
                    $options['query_name'] = $options['query'];
                }
                //Query logic
                $query = !empty($options['query']) ? $options['query'] : false;
                if (!$query && !$this->fw->devoid($options['table'])) {
                    return false;
                } elseif (!$query) {
                    $query = "SELECT * FROM " . $options['table'];
                    //todo try to create a where statement id binding is filled in.
                }
                $binding = !empty($options['bind_array']) ? $options['bind_array'] : null;

                //Pagination logic
                $limit = null;
                if (!empty($options['pagination']['start'])) {
                    $start = $options['pagination']['start'];
                    $limit = " LIMIT $start";
                    if (!$this->fw->devoid($options['pagination']['length'])) {
                        $length = $options['pagination']['length'];
                        $limit .= ", $length";
                    }
                }

                if((!empty($this->queries['name']) && in_array($options['query_name'], $this->queries['name'])) && empty($options['requery'])) {
                    $array = $this->queries['name'][$options['query_name']]['object'];
                } else {
                    $array = $DB->exec($query . $limit, $binding);
                    $this->queries['name'][$options['query_name']]['object'] = $array;
                }

                if (!empty($array)) {
                    //create an object of results for us to work with;
                    $retVal = json_decode(json_encode($array), FALSE);
                }
                break;
            //Use this for reading and writing to the db. i.e. updating a record etc.
            case 'mapper':
            case 'cortex':
            default:
                if ($this->fw->devoid($options['table'])) {
                    error_log('Table name not provided in get_data_as_object operation. Error 409');
                    $retVal = false;
                } else {
                    if ($type = 'mapper') {
                        $retVal = new \DB\SQL\Mapper($DB, $options['table']);
                    } else {
                        //default
                        $table = "\\tables\\" . $options["table"];
                        $retVal = new $table();
                    }
                }

                break;
        }
        return $retVal;
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

    public function get_records_by_key_value()
    {
        $retVal = false;
        if ($args = $this->get_parameters(func_get_args())) {
            $options = array(
                'type' => !$this->fw->devoid($args['type']) ? $args['type'] : 'sql',
            );
            $load = false;
            switch ($options['type']) {
                case 'sql':
                    $options['query'] = !$this->fw->devoid($args['query']) ? $args['query'] : false;
                    $options['bind_array'] = $args['bind_array'];
                    break;
                case 'mapper':
                case 'cortex':
                    $load = true;
                    $options['table'] = !$this->fw->devoid($args['table']) ? $args['table'] : false;
                    break;
            }
            if ($retVal = $this->get_data_as_object($options)) {
                if ($load) {
                    $retVal->load(!$this->fw->devoid($args['bind_array']) ? $args['bind_array'] : false);
                    if ($retVal->dry()) {
                        $retVal = false;
                    }
                }
            }
        }
        return $retVal;
    }

    public function new_record()
    {
        $retVal = false;
        if ($args = $this->get_parameters(func_get_args())) {
            $options = array(
                'type' => (!$this->fw->devoid($args['type']) && $args['type'] != "sql") ? $args['type'] : 'cortex',
                'table' => !$this->fw->devoid($args['table']) ? $args['table'] : false,
            );
            $fields = (!$this->fw->devoid($args['fields']) && is_array($args['fields'])) ? $args['fields'] : false;
            if (!$fields) {
                error_log('Could not add new records to table: ' . $options['table'] . '. Code: 409,  Msg: Missing fields array.');
                return false;
            }
            if ($mapper = $this->get_data_as_object($options)) {
                foreach ($fields as $field => $value) {
                    $mapper->$field = $value;
                }

                try {
                    $mapper->save();
                } catch (\PDOException $e) {
                    error_log('Could not add new records to table: ' . $options['table'] . '. Code: ' . $e->getCode() . ', Msg: ' . $this->fw->stringify($e->getMessage()));
                    return false;
                }
                $retVal = $mapper->id;
                $mapper->reset();
            }
        }
        return $retVal;
    }

    public function update_by_record_id()
    {
        $retVal = false;
        if ($args = $this->get_parameters(func_get_args())) {
            $options = array(
                'type' => (!$this->fw->devoid($args['type']) && $args['type'] != "sql") ? $args['type'] : 'cortex',
                'table' => !$this->fw->devoid($args['table']) ? $args['table'] : false,
            );
            $record_id = !$this->fw->devoid($args['record_id']) ? $args['record_id'] : false;
            if (!$record_id) {
                error_log('Could not add update records to table: ' . $options['table'] . '. Code: 409,  Msg: Missing Record ID.');
                return false;
            }
            $fields = (!$this->fw->devoid($args['fields']) && is_array($args['fields'])) ? $args['fields'] : false;
            if (!$fields) {
                error_log('Could not update records to table: ' . $options['table'] . '. Code: 409,  Msg: Missing fields array.');
                return false;
            }
            if ($mapper = $this->get_data_as_object($options)) {
                $retVal->load(array('id' => $record_id));
                if ($retVal->dry()) {
                    $retVal = false;
                } else {
                    foreach ($fields as $field => $value) {
                        $mapper->$field = $value;
                    }
                    try {
                        $mapper->save();
                    } catch (\PDOException $e) {
                        error_log('Could not add update records to table: ' . $options['table'] . '. Code: ' . $e->getCode() . ', Msg: ' . $this->fw->stringify($e->getMessage()));
                        return false;
                    }
                    $retVal = $mapper->id;
                    $mapper->reset();
                }
            }
            return $retVal;
        }
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

    public function update_each_field_type ($tbl_name, $check_only = false, $DB = false) {
        $DB = $DB ?: $this->db;
        if (!$DB) {
            return false;
        }
        $retVal=false;
        $schema = new \DB\SQL\Schema($DB);
        $child = get_class("\\tables\\".$tbl_name);
        $table_name = $child->$table;
        $cortex_field_definitions = $child->$fieldConf;
        $tableInfo = $schema->alterTable($table_name)->getCols(true);
        foreach($cortex_field_definitions as $columnName => $fieldConf){
            $condition = $tableInfo[$columnName] == $fieldConf['type'];
            $condition = $condition + $schema->isCompatible( $fieldConf['type'], $columnName );
            if($check_only) {
                $retVal = $condition;
            } else if($condition) {
                $tableInfo->updateColumn($columnName, $fieldConf['type'], true);
                $tableInfo->build();
                $retVal = true;
            }
        }
        return $retVal;
    }

    public function escape($value)
    {
        if ($value)
        {
            return "`".str_replace("`","``",$value)."`";
        } else {
            error_log("Cannot escape ".$value.". Operation aborted for the greater good of all mankind. 500");
            die();
        }
    }
}