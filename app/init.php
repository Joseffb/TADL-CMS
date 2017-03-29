<?php

// Kickstart the framework
require_once("../vendor/autoload.php");
$fw= base::instance();
if ((float)PCRE_VERSION<7.9)
    trigger_error('PCRE version is out of date');

//define global variables - start
$fw->set( 'PUBLIC_ROOT', $fw->ROOT );
$fw->set( 'ROOT', str_replace( 'public', 'app/', __DIR__ ) ); //we are fixing the ROOT to be the true root.
$fw->set( 'APP', $fw->get( 'ROOT' )."/" );
$fw->set( 'CONFIGS', $fw->get( 'APP' ) . "configs/" );
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
$fw->config($fw->CORE."system.ini");
//###########################################################

//user configs
$fw->config($fw->CONFIGS."load.cfg");

//initiate system
\controllers\tadl::load_tadl();

//display site
$fw->run();