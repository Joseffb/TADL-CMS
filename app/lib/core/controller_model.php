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
    public $event = false;

    public function __construct()
    {
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

    public function test()
    {
        echo "test";
    }


    public function get_model_path($class_name, $namespace = false, $db_type = false)
    {
        if ($namespace) {
            $namespace = $namespace . "\\";
            $class_name = str_replace($namespace, '', $class_name);
        }
        $db_type = $db_type ?: $this->fw->DB_TYPE;
        $is_sql = $db_type != "jig" && $db_type != "mongo";
        $path = "\\models\\" . $db_type . "\\" . $class_name;
        if ($is_sql && $db_type != 'mysql') {
            if (!class_exists($path)) {
                //allow other sql to default to the mysql variant if there are missing routes for that sql variant.
                $path = "\\models\\mysql\\" . $class_name;
            }
        }
        return $path;
    }

    
    
    
       /**
    * get_select_query
	*
	* This function properly compiles an UPDATE query using events to modify the parameters.
	*
	* Please note that $updatedfields is a an associative array of fields and their new values.
	*
	*
	* @param mixed $table the table name or an array with the parameters
	* @param string $where the WHERE part of the query. Can either be with or without the WHERE.
	*				 Ignored if "WHERE" is found in previous parameters
	* @param array $updatedfields the field names associated to their new values.
	* @param string $limit the LIMIT part of the query. Can either be with or without the LIMIT.
	*				 Ignored if "LIMIT" is found in previous parameters
	* @return string returns the built query 
	* @author: Martin-Pierre Frenette <mpfrenette@gmail.com>
	*/
    public function get_update_query( $table,$where = null, $updatedfields= array(), $limit = null){

    	if ( is_array($table) ){
    		if ( !empty($table['table']) && !is_array($table['table']) ){
    			$args = $table;
    			$table = $args['table'];
				$where = !empty($args['where'])?$args['where']: null;
				$updatedfields = !empty($args['updatedfields'])?$args['updatedfields']: null;
				$limit = !empty($args['limit'])?$args['limit']: null;
    		}
    		else{
    			return false;
    		}
    	}

    	$query = '';

    	// First, process the table and the select parameters.
    	$table = $this->event->emit('controller_model_get_update_query_table_' . $table, $table);
    	if ( strpos($table, 'UPDATE') === false){
			$query .= 'UPDATE '. $table. ' ';
		}
		else{
			// we have a select in the table section, which means that we might have a full query instead!
			$query .= $table. ' ';	
		}

		// second, we process the SET
    	if ( strpos($query, ' SET ') === false){
    		$updatedfields = $this->event->emit('controller_model_get_update_query_set_' . $table, $updatedfields);
			if (!empty($updatedfields) && is_array($updatedfields) && count($updatedfields) > 0)	{

					$setvalues = array();
					foreach($updatedfields as $key => $value)	{
						$setvalues[] = $key.'="'.mysql_real_escape_string($value).'"';
					}

					$query .= ' SET ';
					$query .= implode(',',$setvalues);
			}
		}


		// third, let's handle the where if there is one.
		$where = $this->event->emit('controller_model_get_update_query_where_' . $table, $where);
		if ( !empty($where)){
			if ( strpos($query, 'WHERE') !== false){
				// we already have a WHERE in our query!!! 
			}
			else if ( strpos($where, 'WHERE')){
				// we have a WHERE in our where, so let's just add it directly
				$query .= ' ' . $where;
			}
			else{
				$query .= ' WHERE ' . $where;

			}
    	}

    	// finally, let's handle the LIMIT if there is one.
    	$limit = $this->event->emit('controller_model_get_update_query_limit_' . $table, $limit);
		if ( !empty($limit)){
			if ( strpos($query, 'LIMIT') !== false){
				// we already have a LIMIT in our query!!! 
			}
			else if ( strpos($limit, 'LIMIT')){
				// we have a WHERE in our where, so let's just add it directly
				$query .= ' ' . $limit;
			}
			else{
				$query .= ' LIMIT ' . $limit;

			}
    	}
  		$query = $this->event->emit('controller_model_get_update_query_query_' . $table, $query);

  		return $query;
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
        $this->event->emit('controller_model_get_data_as_object_start', false);
        $this->event->emit('controller_model_get_data_as_object_start_' . $options['query_name'], false);
        $retVal = false;
        if (!empty($options['table']) && !$this->check_if_table_exists($options['table'])) {
            //security check -- make sure the table is in the DB we are have configured and not in the system db or elsewhere.
            return $retVal;
        }
        $DB = !empty($options['DB']) ? $this->fw->get($options['DB']) : $this->db;

        switch ($options['type']) {
            // Use this for reading and pulling data from the db.
            case 'sql':
                if (empty($options['query_name'])) {
                    $options['query_name'] = str_replace(" ", "-", $options['query']);
                }
                //Query logic
                $query = !empty($options['query']) ? $options['query'] : false;
                $where = !empty($options['where']) ? $options['where'] : false;
                if (!$query && empty($options['table'])) {
                    return false;
                } elseif (!$query) {
                    $query = "SELECT * FROM " . $options['table'];
                }
                $binding = !empty($options['bind_array']) ? $options['bind_array'] : null;

                //Pagination logic
                $limit = null;
                if (!empty($options['pagination']['start'])) {
                    $start = $options['pagination']['start'];
                    $limit = " LIMIT $start";
                    if (!empty($options['pagination']['length'])) {
                        $length = $options['pagination']['length'];
                        $limit .= ", $length";
                    }
                }

                if ((!empty($this->queries['name']) && in_array($options['query_name'], $this->queries['name'])) && empty($options['requery'])) {
                    $array = $this->queries['name'][$options['query_name']]['object'];
                } else {
                    $query = $this->event->emit('controller_model_get_data_as_object_sql_query_' . $options['query_name'], $query);
                    $where = $this->event->emit('controller_model_get_data_as_object_sql_where_' . $options['query_name'], $where);
                    $limit = $this->event->emit('controller_model_get_data_as_object_sql_limit_' . $options['query_name'], $limit);
                    $array = $DB->exec($query . $where . $limit, $binding);
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
                if (empty($options['table'])) {
                    error_log('Table name not provided in get_data_as_object operation. Error 409');
                    $retVal = false;
                } else {

                    if (empty($options['query_name'])) {
                        $options['query_name'] = $options['table'];
                    }
                    $table = $this->event->emit('controller_model_get_data_as_object_mapper_table_' . $options['query_name'], $options['table']);
                    if ($options['type'] == 'mapper') {
                        $retVal = new \DB\SQL\Mapper($DB, $table);
                    } else {
                        //default
                        $table = "\\tables\\" . $table;
                        $retVal = new $table();
                    }
                    //complex load and find
                    if(!empty($options['where'])) {
                        $method = $options['method']?$options['method']:'find';
                        $method = $this->event->emit('controller_model_get_data_as_object_mapper_method_' . $options['query_name'], $method);
                        $where = $this->event->emit('controller_model_get_data_as_object_mapper_where_' . $options['query_name'], $options['where']);
                        $bind = $this->event->emit('controller_model_get_data_as_object_mapper_bind_' . $options['query_name'], $options['bind_array']);
                        $bind_val = false;
                        if(!empty($bind)) {
                            if (is_array($bind[0])) {
                                foreach($bind as $k => $v) {
                                    //named parameters
                                    $bind_val[$k] = $v;
                                }
                            } else {
                                //? parameters
                                foreach($bind as $v) {
                                    //named parameters
                                    $bind_val[] = $v;
                                }
                            }
                            $where = array_merge($where,$bind_val);
                        }
                        $retVal = $retVal->$method($where);
                    } elseif (isset($options['load'])) {
                        $retVal = $retVal->load();
                    }
                }
                break;
        }

        $retVal = $this->event->emit('controller_model_get_data_as_object_end_' . $options['query_name'], $retVal);
        $retVal = $this->event->emit('controller_model_get_data_as_object_end', $retVal);
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

    /*
    	This function allows to retrieve records using a custom where or a custom query.
    	Right now, it only supports a single parameter, which has to be an array with either:
    	 - table and where defined, in which case it will search the table with the where
    	 - query in which case it will execute the exact query

    	 If you do not specify the type, sql will be assumed.
    */
	public function get_records_by_key_value()
    {
        $retVal = false;
        if ($args = $this->get_parameters(func_get_args())) {
        	$args = $args[0];

            $options = array(
                'type' => !empty($args['type']) ? $args['type'] : 'sql',
            );
            $load = false;
            switch ($options['type']) {
                case 'sql':
                	if ( !empty($args['query'])){
                    	$options['query'] = $args['query'];
                	}
                	else{
                  		$options['table']  = !empty($args['table']) ? $args['table'] : 'false';
	              		$options['where']  = !empty($args['where']) ? $args['where'] : false;
                	}
                    $options['bind_array'] = !empty($args['bind_array']) ? $args['bind_array'] : false;
                break;
                case 'mapper':
                case 'cortex':
                    $load = true;
                    $options['table'] = !empty($args['table']) ? $args['table'] : false;
                    break;
            }
            if ($retVal = $this->get_data_as_object($options)) {
                if ($load) {
                    $retVal->load(!empty($args['bind_array']) ? $args['bind_array'] : false);
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
                'type' => (!empty($args['type']) && $args['type'] != "sql") ? $args['type'] : 'cortex',
                'table' => !empty($args['table']) ? $args['table'] : false,
            );
            $fields = (!empty($args['fields']) && is_array($args['fields'])) ? $args['fields'] : false;
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
                'type' => (!empty($args['type']) && $args['type'] != "sql") ? $args['type'] : 'cortex',
                'table' => !empty($args['table']) ? $args['table'] : false,
            );
            $record_id = !empty($args['record_id']) ? $args['record_id'] : false;
            if (!$record_id) {
                error_log('Could not add update records to table: ' . $options['table'] . '. Code: 409,  Msg: Missing Record ID.');
                return false;
            }
            $fields = (!empty($args['fields']) && is_array($args['fields'])) ? $args['fields'] : false;
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
        $c = new controller_model();
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
