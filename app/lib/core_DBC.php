<?php

/*
 * This is the core DB Connection file.
 */
namespace lib;
class core_DBC extends \Prefab
{
    public $fw = false;
    public $host = false;
    public $user_name = false;
    public $password = false;
    public $port = false;
    public $db_name = false;
    public $db = false;

    public function __construct()
    {
        $this->fw = $fw = \Base::instance();

        // support multiple db configurations.
        // As such we need to know the number of databases to loop while
        $cnt = 0;
        if (utils::not_void('DB_COUNT')) {
            $cnt = $fw->DB_COUNT;
        } else {
            foreach ($fw->database as $obj) {
                if (is_array($obj)) {
                    $cnt++;
                }
            }
        }
        $db_config = array();
        $do_connection = false;
        foreach ($fw->database as $k => $v) {
            if (is_array($v)) {
                $db_config = $v;
                $this->connect_db($db_config);
            } else {
                $db_config[$k] = $v;
                if ($res = $this->check_db_config($db_config)) {
                    $do_connection = true;
                    if ($res == 99) {
                        break;
                    }
                }
            }
        }
        if ($do_connection) {

            $this->connect_db($db_config);
        }
    }

    public
    function check_db_config($db_config)
    {

        $name = !empty($db_config['DB_NAME']) ? true : false;
        $user = !empty($db_config['DB_USER']) ? true : false;
        $pass = !empty($db_config['DB_PASSWORD']) ? true : false;
        $type = !empty($db_config['DB_TYPE']) ? true : false;
        $port = !empty($db_config['DB_PORT']) ? true : false;

        //echo !$this->fw->devoid($db_config['DB_USER']) . "name: ".$db_config['DB_NAME']." | user ". $db_config['DB_USER'] ." | pass ".$db_config['DB_PASSWORD']. " | type ".$db_config['DB_TYPE']. " <br/>";
        if ($name && $user && $pass && $type && $port) {
            return 99;
        } elseif ($name && $user && $pass && $type) {
            return 88;
        }
        return false;
    }

    public
    function connect_db($db_config)
    {
        $session_table = $this->fw->exists('SESSION_TABLE_NAME') ? $this->fw->SESSION_TABLE_NAME : "SESSION_DATA";
        if (!$this->fw->DEVOID('CACHE')) {
            //todo determine if this is a local cache
            new \Session();
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
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\SQL\Session($db, $session_table, true);
                }
                break;
            case 'sqlsrv':
                $test = !$host &&
                    !$name &&
                    !$user &&
                    !$pass;
                $options = array();
                $port = !$port ? "," . $port : null;
                $db = $test ? new \DB\SQL($type . ':Server=' . $host . $port . ';dbname=' . $db_name, $user_name, $pass, $options) : false;
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\SQL\Session($db, $session_table, true);
                }
                break;
            case 'odbc_driver':
                $test = !$host &&
                    !$name &&
                    !$user &&
                    !$pass;
                $port = $port ?: 50000;
                $db = $test ? new \DB\SQL('odbc:' . $host) : false;
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\SQL\Session($db, $session_table, true);
                }
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
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\SQL\Session($db, $session_table, true);
                }
                break;
            case 'msaccess':
                $test = $host != 'localhost' && !$user;
                $db = $test ? new \DB\SQL('odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $host . ';Uid=' . $user) : false;
                new \DB\SQL\Session($db, $session_table, true);
                break;
            case 'Oracle':
                $test = !$host && !$name;
                $port = $port ?: 1521;
                if (!empty($db_config['DB_ORACLE_USE_ORA'])) {
                    $db = $test ? new \DB\SQL('oci:dbname=' . $name) : false;
                } else {
                    $db = $test ? new \DB\SQL('oci:dbname=//' . $host . ':' . $port . '/' . $name) : false;
                }
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\SQL\Session($db, $session_table, true);
                }
                break;
            case 'IBM_DB2':
                $test = !$host &&
                    !$name &&
                    !$user &&
                    !$pass;
                $port = $port ?: 50000;
                $db = $test ? new \DB\SQL('odbc:DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME=' . $host . ';PORT=' . $port . ';DATABASE=' . $name . ';PROTOCOL=TCPIP;UID=' . $user . ';PWD=' . $pass . ';') : false;
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\SQL\Session($db, $session_table, true);
                }
                break;
            case 'pgsql':
                $test = !$host &&
                    !$name &&
                    !$user &&
                    !$pass;
                $port = $port ?: 5432;
                $db = $test ? new \DB\SQL('pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';user=' . $user . ';password=' . $pass) : false;
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\SQL\Session($db, $session_table, true);
                }
                break;
            case 'mysql':
                $test = $host &&
                    $name &&
                    $user &&
                    $pass;

                $port = $port ?: 3302;
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

                if (!empty($db) && !$this->fw->DEVOID('CACHE')) {
                    new \DB\SQL\Session($db, $session_table, true);
                }
                break;
            case 'mongo':
                $options = array();
                // tiger-todo add options from the mongo php class
                $test = !$host &&
                    !$port &&
                    !$name;
                $db = $test ? new \DB\MONGO('mongodb://' . $host . ':' . $port, $name) : false;
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\MONGO\Session($db, $session_table, true);
                }
                break;
            case 'jig':
                $host = $host != 'localhost' ? $host : $fw->TEMP . "jig_data";
                $format = null;
                if (!empty($db_config['DB_JIG_FORMAT']) && $db_config['DB_JIG_FORMAT'] == "Serialized") {
                    $format = \DB\Jig::FORMAT_Serialized;
                } elseif (!empty($db_config['DB_JIG_FORMAT']) && $db_config['DB_JIG_FORMAT'] == "JSON") {
                    $format = \DB\Jig::FORMAT_JSON;
                }
                $db = $test ? new \DB\Jig ($host, $format) : false;
                if (!$this->fw->DEVOID('CACHE')) {
                    new \DB\JIG\Session($db, $session_table, true);
                }
                break;
            default:
                //no idea how to handle so we do nothing and allow a return of db false.
                break;
        }
        if (!$db) {
            return false;
        }

        //we keep track of all the database connection names (not object) in the registry.
        $databases = $this->fw->get('DATABASES');
        $db_count = count($databases);
        $databases[] = 'DB' . $db_count++;
        $databases = $this->fw->set('DATABASES', $databases);
        //keep track of each individual db connection (object) in a variable DB0, DB1, DB2, etc
        $this->fw->set('DB' . $db_count, $db);
        //DB0 will get the honor of being the default DB. If the DEFAULT config switch is set to true this will overwrite DB0 as the default.
        if ($db && ($db_count == 1 || !empty($db_config['DB_DEFAULT']))) {
            $this->fw->set('DB', $db);
        }
        return $db;
    }

    static public function class_test()
    {
        return true;
    }
}
