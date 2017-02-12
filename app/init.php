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
$fw->set( 'UI', $fw->get( 'APP' ) . "ui/" );
$fw->set( 'TEMPLATES_JSON', $fw->get( 'APP' ) . "ui/json/" );
$fw->set( 'TEMPLATES_EMAIL', $fw->get( 'APP' ) . "ui/emails/" );
$fw->set( 'TEMPLATES_SITE', $fw->get( 'APP' ) . "ui/templates/" );
//define global variables - stop

$fw->config($fw->CONFIGS."load.cfg");


$fw->run();