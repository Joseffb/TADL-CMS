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


use utils\debug;

class controller extends \Prefab
{

    public $fw, $db;
    protected $queries = array();
    public $event = false;
    public $model = false;

    public function __construct()
    {
        $DB = false;
        $this->fw = \Base::instance();
        $this->model = new model();
        $this->event = new \Event();
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
     * @param array $options
     * @return array|bool|\DB\SQL\Mapper|FALSE|mixed
     */
    public function get_data(array $options)
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
        //Fire general start event for function
        $this->event->emit('controller_get_data_start', false);
        $ttyl = $this->event->emit('controller_get_data_ttyl', $options['ttyl']);
        $ttyl = !empty($ttyl) && !$this->fw->devoid('QUERY_TTYL')?$this->fw->QUERY_TTYL:false;
        $table = $this->event->emit('controller_get_data_mapper_table_' . $options['query_name'], $options['table']);
        $query_name = !empty($options['query_name']) ? $options['query_name'] : $table;
        //Fire start event specific to this query
        $this->event->emit('controller_get_data_start_' . $query_name, false);
        $method = !empty($options['method'])?$options['method']:'find';
        $retVal = false;
        if (!empty($table) && !$this->model->check_if_table_exists($table)) {
            //Is this only necessary in a db->exec or in a mapper too. TODO comment out and attempt to modify a mysql system table with this via mapper
            //security check -- make sure the table is in the DB have configured and not in the system db or elsewhere.
            return $retVal;
        }

        if (empty($table)) {
            $msg = "Table name not provided in get_data operation. Error 1409";
            \utils\debug::write_log($msg, true);
            die('');
        }

        //Load or star the db connection
        $DB = !empty($options['DB']) ? $this->fw->get($options['DB']) : $this->db;

        //build our values
        $where = !empty($options['$where'])?$this->build_where_statement($options['where'], $options['bind'], $query_name):null;
        $orderBy = !empty($options['orderBy'])?$this->build_order_statement($options['orderBy'], $query_name):false;
        $groupBy = !empty($options['groupBy'])?$this->build_group_statement($options['groupBy'], $query_name):false;
        $limitBy = !empty($options['limit'])?$this->build_limit_statement($options['limit'], $query_name):false;
        $offsetBy = !empty($options['offset'])?$this->build_offset_statement($options['offset'], $query_name):0;
        $opts = false;
        if ($orderBy || $groupBy || $limitBy) {
            $opts = array();
            if ($orderBy) {
                $opts['order'] = $orderBy;
            }
            if ($groupBy) {
                $opts['group'] = $groupBy;
            }
            if ($limitBy) {
                $opts['limit'] = (int) $limitBy;
                $opts['offset'] = (int) $offsetBy;
            }
        }

        if ($options['type'] == 'mapper') {
            $retVal = new \DB\SQL\Mapper($DB, $table);
        } else {
            //default
            $table = "\\tables\\" . $table;
            $retVal = new $table();
        }
        $opts = $opts?:null;
        if ($method == 'load') {
            $retVal = $retVal->load($where, $opts, $ttyl);
        } else  {
            $retVal = $retVal->find($where, $opts, $ttyl);
        }

        $retVal = $this->event->emit('controller_get_data_end_' . $options['query_name'], $retVal);
        return $this->event->emit('controller_get_data_end', $retVal);
    }

    public function build_where_statement($where, $bind, $name = false)
    {
        $name = $name ? "_" . $name : '';
        $where = $this->event->emit('controller_build_where_statement_where' . $name, $where);
        if (empty($where)) {
            $msg = 'You must have a where array to build a where statement. query' . $name . "_error: 1410";
            \utils\debug::write_log($msg, true);
        }
        $bind = $this->event->emit('controller_build_where_statement_bind' . $name, $bind);
        $bind_val = false;
        if (!empty($bind)) { //can be emptied in the event call.
            if (is_array($bind[0])) {
                foreach ($bind as $k => $v) {
                    //named parameters
                    $bind_val[$k] = $v;
                }
            } else {
                //? parameters
                foreach ($bind as $v) {
                    //named parameters
                    $bind_val[] = $v;
                }
            }
            $where = array_merge($where, $bind_val);
        }

        return $this->event->emit('controller_build_where_statement_end' . $name, $where);
    }

    public function build_limit_statement($limit, $name = false)
    {
        $name = $name ? "_" . $name : '';
        return $this->event->emit('controller_build_limit_statement_end' . $name, $limit);
    }

    public function build_offset_statement($offset, $name = false)
    {
        $name = $name ? "_" . $name : '';
        return $this->event->emit('controller_build_offset_statement_end' . $name, $offset);
    }

    public function build_order_statement(array $order = array(), $name = false)
    {
        $retVal = false;
        $name = $name ? "_" . $name : '';

        $new_order = array();
        $order = $this->event->emit('controller_build_order_statement_start' . $name, $order);
        if(!empty($order)) {
            return false;
        }
        //looking for
        // array ( field => ASC )
        foreach ($order as $k => $v) {
            $new_order[] = $k . " " . $v;
        }
        $retVal = implode(",", $new_order);
        return $this->event->emit('controller_build_order_statement_end' . $name, $retVal);
    }

    public function build_group_statement(array $group = array(), $name = false)
    {
        $name = $name ? "_" . $name : '';
        $group = $this->event->emit('controller_build_group_statement_start' . $name, $group);
        if(!empty($group)) {
            return false;
        }
        //looking for
        // array ( field, field, field )
        $retVal = implode(",", $group);

        return $this->event->emit('controller_build_group_statement_end' . $name, $retVal);
    }




}
