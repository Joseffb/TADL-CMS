<?php
  /**
   * Created by PhpStorm.
   * User: betajo01
   * Date: 9/25/16
   * Time: 7:09 PM
   */

  namespace controllers;
  class test extends \Test {
	public $f3 = FALSE;
	public $db = FALSE;
	public $prefix = FALSE;
	public $testFile = FALSE;

	public function __construct() {
	  $f3 = $this->f3 = \Base::instance();
	}



  }