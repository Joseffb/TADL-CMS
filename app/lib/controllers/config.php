<?php
namespace controllers;

class config
{
    private $fw = false;

    function __construct()
    {
        $this->fw = \Base::instance();
        //$this->fw->set('DEBUG', 4);
    }

    static function load($cfg = false, $array_name = false)
    {
        $c = new config();
        $c->append_autoloader($cfg, $array_name);
    }

    function config($cfg = false)
    {
        $cfg = $cfg ?: $this->fw->CONFIGS . "user.cfg";
        if (file_exists($cfg)) {
            $this->fw->config($cfg);
            return true;
        }
        return false;
    }

    function append_autoloader($cfg = false, $array_name = false)
    {
        if(!$this->config($cfg)) {
            //no custom config file so we don't continue.
            return false;
        }
        $array_name = !empty($array_name) ? $array_name : 'user_load';
        //remove it and then add it back.
        $retVal = "nope<br/>";
        $target = $this->fw->get($array_name);
        if ($target) {
            foreach ($target as $k => $v) {
                $key = $this->fw->$k;
                if ($k == "AUTOLOAD") {
                    //Autoload gets items appended to the string
                    //clean the string
                    $a = $this->fw->get("AUTOLOAD");
                    $autoload = rtrim($a, ';') . ';';
                    $autoload .= (string)$v . ';';
                    $this->fw->set("AUTOLOAD", $autoload);
                    $retVal = $this->fw->$k;
                } else {
                    //everything else gets replaced (so far).
                    $this->fw->set(ucwords($k), $v);
                    $retVal = $this->fw->$k;
                }
            }
        }
        return $retVal;
    }

}