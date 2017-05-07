<?php
namespace controllers;

class theme extends \core\controller
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
	    switch($type) {
		    case 'global':
			    $ns = 'gl';
			    break;
		    case 'group':
			    $ns = 'gp';
			    break;
	    }
        $js_array = $this->get_jsArray($type , $id)?:array();
        $js_array[$var] = $value;
        return $this->fw->set('LOCALIZED_JS.'.$ns, $js_array);
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
            $this->set_js_var($var, $value, $type, $id);
        }
        return false;
    }

    function process_js_array_values ($js_array)
    {
        foreach ($js_array as $k => $v) {
            if (!is_array($v)) {
                $fill = $k . ":" . $v . ",";
            } else {
                return $k . ": { " . $this->process_js_array_values($v) . "},";
            }
        }
    }

    public static function get_localized_js($type = 'global', $id = false, $tags = true)
    {
        $t = new theme();
        $js_array = $t->get_jsArray();
        if($type != 'global') {
            $js_array = array_merge($js_array, $t->get_jsArray($type , $id));
        }
        $script = false;
        if (!empty($js_array) && is_array($js_array)) {
            $script = $t->set_global_js($js_array, $tags);
        }
        return $script;
    }

    static function load_vue_js ($folder_name, $ui_folder, $theme_url, $HOST = false, $header = true ) {
        $dir = self::load_vue_path($folder_name, $ui_folder, $theme_url);
        $retVal = self::load_vue_templates($dir[0], $dir[1]);
        $retVal .= self::load_vue_components($dir[0], $header);
        return $retVal;
    }

    static function load_vue_path ($folder_name, $ui_folder, $theme_url, $HOST = false) {
        $path = $ui_folder  . $theme_url . $folder_name . '/';
        $dir = new \DirectoryIterator( $path );
        //$i = new auth();

        //get the names
        $names = array();
        foreach ( $dir as $fileinfo ) {
            if ( ! $fileinfo->isDot() ) {
                $names[] = $fileinfo->getFilename();
            }
        }

        return array($names, $path);
    }

    static function load_vue_templates ($names, $path, $header = true) {
        //set up the templates based on the names given
        $retVal = "";
        $footer = "";
        foreach ( $names as $name ) {
            if ($header) {
                $header = '<script type="text/x-template" id="' . str_replace( '.template', '', $name ) . '">';
                $footer = "</script>";
            }
            $script = $header;
            //echo "test;";
            //var_dump( $path . $name );
            $script .= file_get_contents( $path . $name );
            $script .= $footer;
            $retVal .= $script;
        }
        return $retVal;
    }

    static function load_vue_components ($names, $header = true) {
        //Set up the basic components based on the names given
        $retVal = "";
        $footer = "";
        if ($header) {
            $header = "<script type='application/javascript'>";
            $footer = "</script>";
        }

        foreach ( $names as $name ) {
            $s_name = str_replace( '.template', '', $name );

            $script = $header .
                    "Vue.component('$s_name', {
                    template: '#$s_name',
                      data: function() {
						    return {
						      CMS: TADL[0]
						    }
						  }
                });\n ". $footer;
            $retVal .= $script;;
        }
        return $retVal;
    }

	function set_global_js( array $js_array, $tags = true ) {
		$header = "";
        $footer = "";
        if($tags) {
            $header = "<script>";
            $footer = "</script>";
        }
        $r = $header;
		$r .= 'var TADL =  [];';
		$p = array();
		$r .= 'TADL.push({';
		$cnt = 0; $jsCnt = count($js_array);
		foreach ( $js_array as $k => $v ) {
			$k = json_decode(json_encode($k));
			$v = json_decode(json_encode($v));
			$p[$k] = $v;
			$r .= "$k: '$v'";
			if($cnt < $jsCnt) {
				$r .= ", ";
			}
			$cnt++;
		}

		$r .= '})';
		$r .= $footer;
		return $r;
	}
}