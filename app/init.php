<?php

// Kickstart the framework
$fw= require('../f3/base.php');
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

$fw->config($fw->CONFIGS."load.cfg");
$fw->set("SITE_ID", 0);
\controllers\tadl::load_tadl();

$fw->run();