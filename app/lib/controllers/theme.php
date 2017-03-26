<?php
namespace controllers;

class theme extends \core\controller_model
{

    private $js_namespace = "GLJS";

    function get_jsArray( $type = 'global', $id = false) {
        switch($type) {
            case 'global':
                $ns = 'gl';
                $js_array = !$this->fw->devoid('LOCALIZED_JS.gl') ? $this->fw->get('LOCALIZED_JS.gl') : array();
                break;
            case 'group':
                $ns = "gr:$id";
                $js_array = !$this->fw->devoid("LOCALIZED_JS.$ns") ? $this->fw->get("LOCALIZED_JS.$ns") : array();
                break;
        }
        return !$this->fw->devoid("LOCALIZED_JS.$ns") ? $this->fw->get("LOCALIZED_JS.$ns") :false;
    }

    function set_js_var($var, $value, $type = 'global', $id = false)
    {
        $js_array = $this->get_jsArray($type , $id)?:array();
        $js_array = $js_array[$var] = $value;
        return $this->fw->set('LOCALIZED_JS', $js_array);
    }

    function update_js_var($var, $value, $type = 'global', $id = false)
    {
        $js_array = $this->get_jsArray($type , $id);
        if (!empty($js_array) && is_array($js_array)) {
            foreach ($js_array as $k => $v) {
                if ($k === $var) {
                    $js_array[$k] = $value;
                    $this->fw->clear('LOCALIZED_JS');
                    $this->fw->set('LOCALIZED_JS', $js_array);
                }
            }
            return true;
        } else {
            $this->set_js_var($var, $value, $namespace, $type , $url);
        }
        return false;
    }

    function process_js_array_values ($js_array)
    {
        foreach ($js_array as $k => $v) {
            if (!is_array($v)) {
                $fill = $k . ":" . $v . ",";
            } else {
                return $k . ": { " . $this->process_array_for_js($v) . "},";
            }
        }
    }

    public static function get_localized_js($type = 'global', $id = false)
    {
        $t = new theme();
        $js_array = $t->get_jsArray();
        if($type != 'global') {
            $js_array = array_merge($js_array, $t->get_jsArray($type , $id));
        }
        $script = false;;
        if (!empty($js_array) && is_array($js_array)) {
            $script .= "<script>";
            $script .= "var $t->js_namespace = {";
            $script .= $t->process_js_array_values($js_array);
            $script .= "}";
            $script .= "</script>";
        }
        return $script;
    }
}