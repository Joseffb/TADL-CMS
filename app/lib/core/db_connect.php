<?php

/*
 * This is the core DB Connection file.
 */
namespace core;
class db_connect extends \Prefab
{
    public $fw = false;
    public $host = false;
    public $user_name = false;
    public $password = false;
    public $port = false;
    public $db_name = false;
    public $db = false;
    public $event = false;

    public function __construct()
    {
        $this->fw = $fw = \Base::instance();
        $this->event = new \Event();
        // support multiple db configurations.
        // As such we need to know the number of databases to loop while
        $cnt = 0;
        if (!$fw->devoid('DB_COUNT')) {
            $cnt = $fw->DB_COUNT;
        } else {
            if($fw->devoid('database')) {
                $fw->config($fw->CONFIGS."environment.ini");
                if($fw->devoid('database')) {
                    $msg = 'Error: DB configuration missing.';
                    $msg = $this->event->emit('db_not_config_found_message',$msg);
                    die($msg);
                }
            }
            foreach ($fw->database as $obj) {
                if (is_array($obj)) {
                    $cnt++;
                }
            }
        }
        $db_config = array();
        foreach ($fw->database as $k => $v) {
            if (is_array($v)) {
                $db_config = $v;
                $this->connect_db($db_config);
            } else {
                $db_config[$k] = $v;
                if ($res = $this->check_db_config($db_config)) {
                    $this->connect_db($db_config);
                    if ($res == 99) {
                        break;

                    }
                }
            }
        }

        //allows a plugin to add a database config programmaticly
        $extra_db_configs = false;
        $extra_db_configs = $this->event->emit('db_connect_extra_configs',$extra_db_configs);
        if(!empty($extra_db_configs)) {
            foreach($extra_db_configs as $db_config) {
                $this->connect_db($db_config);
            }
        }

    }
        static function boot($dbID = 0)
    {
        $c = new db_connect();
        $c->event->emit('db_connect_boot',false);
        return $c->fw->get('DB' . $dbID);
    }

    public
    function check_db_config($db_config)
    {
        $this->event->emit('check_db_config_start', false);
        $name = !empty($db_config['DB_NAME']) ? true : false;
        $user = !empty($db_config['DB_USER']) ? true : false;
        $pass = !empty($db_config['DB_PASSWORD']) ? true : false;
        $type = !empty($db_config['DB_TYPE']) ? true : false;
        $port = !empty($db_config['DB_PORT']) ? true : false;
        $retVal = false;
        //echo !$this->fw->devoid($db_config['DB_USER']) . "name: ".$db_config['DB_NAME']." | user ". $db_config['DB_USER'] ." | pass ".$db_config['DB_PASSWORD']. " | type ".$db_config['DB_TYPE']. " <br/>";
        if ($name && $user && $pass && $type && $port) {
            $retVal = 99;
        } elseif ($name && $user && $pass && $type) {
            $retVal = 88;
        }
        $retVal = $this->event->emit('db_connect_check_db_config_parse',$retVal);
        $retVal = $this->event->emit('db_connect_check_db_end',$retVal);
        return $retVal;
    }

    public
    function connect_db($db_config)
    {
        $this->event->emit('db_connect_start', false);
        $session_table = $this->fw->exists('SESSION_TABLE_NAME') ? $this->fw->SESSION_TABLE_NAME : "SESSION_DATA";
        if (!$this->fw->DEVOID('CACHE')) {
            //todo determine if this is a local cache
            $localCache = 'Session';
            $localCache = '\\'.$this->event->emit('db_name_of_local_cache',$localCache);
            new $localCache();
        }
        $fw = $this->fw;
        $host = !empty($db_config['DB_HOST']) ? $db_config['DB_HOST'] : 'localhost';
        $port = !empty($db_config['DB_PORT']) ? $db_config['DB_PORT'] : false;
        $name = !empty($db_config['DB_NAME']) ? $db_config['DB_NAME'] : false;
        $user = !empty($db_config['DB_USER']) ? $db_config['DB_USER'] : false;
        $pass = !empty($db_config['DB_PASSWORD']) ? $db_config['DB_PASSWORD'] : false;
        $type = !empty($db_config['DB_TYPE']) ? strtolower($db_config['DB_TYPE']) : false;
        $db = false;

        switch ($type) {
            case 'sqllite':
                $db = $host ? new \DB\SQL('sqlite:' . $host) : false;

                break;
            case 'sqlsrv':
                $test = !$host &&
                    !$name &&
                    !$user &&
                    !$pass;
                $options = array();
                $port = !$port ? "," . $port : null;
                $db = $test ? new \DB\SQL($type . ':Server=' . $host . $port . ';dbname=' . $db_name, $user_name, $pass, $options) : false;
                break;
            case 'odbc_driver':
                $test = !$host &&
                    !$name &&
                    !$user &&
                    !$pass;
                //$port = $port ?: 50000;
                $db = $test ? new \DB\SQL('odbc:' . $host) : false;

                break;
            case 'mssql':
            case 'dblib':
            case 'sybase':
                $options = array();
                if (!empty($db_config['DB_CHARSET'])) {
                    $options[\PDO::CHARSET] = TRUE;
                }
                if (!empty($db_config['DB_APPNAME'])) {
                    $options[\PDO::APPNAME] = $db_config['DB_APPNAME'];
                }
                $test = !$host && !$name;
                $db = $test ? new \DB\SQL($type . ':host=' . $host . ';dbname=' . $db_name, null, null, $options) : false;
                $type = "mssql";
                break;
            case 'msaccess':
                $test = $host != 'localhost' && !$user;
                $db = $test ? new \DB\SQL('odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $host . ';Uid=' . $user) : false;
                break;
            case 'Oracle':
                $test = !$host && !$name;
                $port = $port ?: 1521;
                if (!empty($db_config['DB_ORACLE_USE_ORA'])) {
                    $db = $test ? new \DB\SQL('oci:dbname=' . $name) : false;
                } else {
                    $db = $test ? new \DB\SQL('oci:dbname=//' . $host . ':' . $port . '/' . $name) : false;
                }
                break;
            case 'IBM_DB2':
                $test = !$host &&
                    !$name &&
                    !$user &&
                    !$pass;
                $port = $port ?: 50000;
                $db = $test ? new \DB\SQL('odbc:DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME=' . $host . ';PORT=' . $port . ';DATABASE=' . $name . ';PROTOCOL=TCPIP;UID=' . $user . ';PWD=' . $pass . ';') : false;

                break;
            case 'pgsql':
                $test = !$host &&
                    !$name &&
                    !$user &&
                    !$pass;
                $port = $port ?: 5432;
                $db = $test ? new \DB\SQL('pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';user=' . $user . ';password=' . $pass) : false;

                break;
            case 'mysql':
                $test = $host &&
                    $name &&
                    $user &&
                    $pass;

                $port = $port ?: 3306;
                $options = array();

                if (!empty($db_config['DB_ERRMODE'])) {
                    $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
                }

                if (!empty($db_config['DB_PERSISTENT'])) {
                    $options[\PDO::ATTR_PERSISTENT] = TRUE;
                }

                if (!empty($db_config['DB_MYSQL_COMPRESS']) && $type == "mysql") {
                    $options[\PDO::MYSQL_ATTR_COMPRESS] = TRUE;
                }
                $db = $test ? new \DB\SQL($type . ':host=' . $host . ';port=' . $port . ';dbname=' . $name, $user, $pass, $options) : false;
                break;
            case 'mongo':
                $options = array();
                // tiger-todo add options from the mongo php class
                $test = !$host &&
                    !$port &&
                    !$name;
                $db = $test ? new \DB\MONGO('mongodb://' . $host . ':' . $port, $name) : false;
                break;
            case 'jig':
                $test = $host != 'localhost' ? $host : $fw->TEMP . "jig_data";
                $format = null;
                if (!empty($db_config['DB_JIG_FORMAT']) && $db_config['DB_JIG_FORMAT'] == "Serialized") {
                    $format = \DB\Jig::FORMAT_Serialized;
                } elseif (!empty($db_config['DB_JIG_FORMAT']) && $db_config['DB_JIG_FORMAT'] == "JSON") {
                    $format = \DB\Jig::FORMAT_JSON;
                }
                $db = $test ? new \DB\Jig ($host, $format) : false;
                break;
            default:
                //no idea how to handle so we do nothing and allow a return of db false.
                break;
        }
        if (!$db) {
            return false;
        }

        if (!$this->fw->DEVOID('CACHE')) {
            $session_db = $this->event->emit('db_connect_session_db', $db);
            $session_table = $this->event->emit('db_connect_session_table_name', $session_table);
            $session_callable = $this->event->emit('db_connect_session_table_name', true);
            if($type == 'mongo') {
                new \DB\MONGO\Session($session_db, $session_table, $session_callable);
            } elseif($type == 'jig') {
                new \DB\JIG\Session($session_db, $session_table, $session_callable);
            } else {
                new \DB\SQL\Session($session_db, $session_table, $session_callable);
            }
        }

        //we keep track of all the database connection names (not object) in the registry.
        $databases = $this->fw->get('DATABASES');
        $db_count = count($databases);
        $databases['DB_INFO'][] = array('DB' . $db_count => $type);
        $this->fw->set('DATABASES', $databases);
        //keep track of each individual db connection (object) in a variable DB0, DB1, DB2, etc
        $this->fw->set('DB' . $db_count, $db);
        //DB0 will get the honor of being the default DB. If the DEFAULT config switch is set to true this will overwrite DB0 as the default.
        if ($db && ($db_count == 0 || !empty($db_config['DB_DEFAULT']))) {
            $this->fw->set('DB', $db);
            $this->fw->set('DB_TYPE', $type);
        }
        $db = $session_db = $this->event->emit('db_connect_end', $db);
        return $db;
    }

    static public function class_test()
    {
        return true;
    }
}
