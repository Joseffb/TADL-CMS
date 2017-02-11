<?php

// Kickstart the framework
$fw= require('../f3/base.php');

//define global variables - start
$fw->set( 'PUBLIC_ROOT', $fw->ROOT );
$fw->set( 'ROOT', str_replace( 'public', 'app/', __DIR__ ) ); //we are fixing the ROOT to be the true root.
$fw->set( 'APP', $fw->get( 'ROOT' )."/" );
$fw->set( 'CONFIGS', $fw->get( 'APP' ) . "configs/" );
$fw->set( 'LIB', $fw->get( 'APP' ) . "lib/" );
$fw->set( 'UI', $fw->get( 'APP' ) . "ui/" );
$fw->set( 'TEMPLATES_JSON', $fw->get( 'APP' ) . "ui/json/" );
$fw->set( 'TEMPLATES_EMAIL', $fw->get( 'APP' ) . "ui/emails/" );
$fw->set( 'TEMPLATES_SITE', $fw->get( 'APP' ) . "ui/templates/" );
$fw->set( 'TEMP', $fw->get( 'APP' ) . "temp/" );
//define global variables - stop

//start load config

$fw->config($fw->CONFIGS."load.cfg");
//stop stop config
$controllerPath = $fw->APP;

$t = new lib\core_cm();
//$t->test();
if ((float)PCRE_VERSION<7.9)
    trigger_error('PCRE version is out of date');

// todo before route check for required f3 libraries.
//$fw->route('GET /',
//    function($fw) {
//        echo "<h3>Works</h3>";
//    }
//);
//$fw->map('/json','lib\json');
//$f3->set('ONERROR','Controller->method');
$fw->run();