<?php

// Kickstart the framework
$path = dirname(realpath(__DIR__))."/";
require_once($path."vendor/autoload.php");
$fw= base::instance();
$fw->set('REALPATH',$path);
if ((float)PCRE_VERSION<7.9)
    trigger_error('PCRE version is out of date');

//define global variables - start
$fw->set( 'PUBLIC_ROOT', $path."public/" );
$fw->set( 'ROOT', $path); //we are fixing the ROOT to be the true root.
$fw->set( 'APP', $fw->get( 'ROOT' )."app/" );
$fw->set( 'CONFIGS', $fw->get( 'APP' ) . "configs/" );
$fw->set( 'PLUGINS', $fw->get( 'APP' ) . "extensions/" );
$fw->set( 'LIB', $fw->get( 'APP' ) . "lib/" );
$fw->set( 'CONTROLLERS', $fw->get( 'LIB' ) . "controllers/" );
$fw->set( 'CORE', $fw->get( 'LIB' ) . "core/" );
$fw->set( 'MODELS', $fw->get( 'LIB' ) . "models/" );
$fw->set( 'TABLES', $fw->get( 'LIB' ) . "tables/" );
$fw->set( 'UTIL', $fw->get( 'LIB' ) . "util/" );
$fw->set( 'UI', $fw->get( 'APP' ) . "ui/" );
$fw->set( 'THEMES_JSON', $fw->get( 'UI' ) . "json/" );
$fw->set( 'THEMES_EMAIL', $fw->get( 'UI' ) . "emails/" );
$fw->set( 'THEMES_SITE', $fw->get( 'UI' ) . "themes/" );
//define global variables - stop

//Do not modify this config unless you know what you're doing
$fw->config($fw->CORE."system.ini", TRUE);
//###########################################################

//user configs
\controllers\config::load();

//this is a test function -- comment out or delete for a prod system
//\extensions\test::one();
//###########################################################

//initiate system
\controllers\tadl::load_tadl();

//display site
$fw->run();
