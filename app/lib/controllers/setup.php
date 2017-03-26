<?php

/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 9:48 AM
 */

namespace controllers;

class setup extends \core\controller_model
{
    public static function json_register()
    {
        tadl::register('controllers', 'setup', 'tables_install', array('GET'), 'exposed','sets up all tables using cortex',
            array(
                array('name' => 'f3', 'type' => 'object'),
            )
        );

        tadl::register('controllers', 'setup', 'table_install', array('POST'), 'exposed','sets up a single table using cortex',
            array(
                array('name' => 'f3', 'type' => 'object'),
            )
        );
        //event_wadl_register_return
    }

    public function tables_install($f3 = false)
    {
        //Todo check if super admin
        $fw = $f3?:$this->fw;
        $dir = new \DirectoryIterator($fw->TABLES);
        $retVal = array();
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $name = str_replace(".php", "", $fileinfo->getFilename());
                $class = "\\tables\\" . $name;
                $this->table_install($class);
                $retVal[]['name']=$name;
                $class = NULL;
            }
        }
        return $retVal;
    }

    public function table_install($table)
    {
        //Todo check if super admin
        $table::setup();

        return true;
    }
}
