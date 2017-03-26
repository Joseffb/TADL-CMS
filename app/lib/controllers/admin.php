<?php
namespace controllers;

class admin extends \core\controller_model {

    function admin_root($theme) {
      echo 'test';
    }

    function set_mobile_theme($theme) {
        //todo event
        $this->fw->set('MOBILE_THEME', $theme);
        //todo event
        $this->fw->set('MOBILE_THEME_URL', $this->fw->UI."\\".$theme);
    }

    function set_frontend_theme($theme) {
        //todo event
        $this->fw->set('FRONTEND_THEME', $theme);
        //todo event
        $this->fw->set('FRONTEND_THEME_URL', $this->fw->UI."\\".$theme);
    }

}