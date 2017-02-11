<?php
  /**
   * Created by PhpStorm.
   * User: betajo01
   * Date: 9/25/16
   * Time: 7:09 PM
   */

  namespace lib;
  class test extends \Test {
	public $f3 = FALSE;
	public $db = FALSE;
	public $prefix = FALSE;
	public $testFile = FALSE;

	public function __construct() {
	  $f3 = $this->f3 = \Base::instance();
	  $this->prefix = $f3->exists('PREFIX') ? $f3->get('PREFIX') : FALSE;

	}

	public function loadFile($className, $type) {
	  //type is either controllers, models, or tables
	  include_once($this->f3->APP."/".$this->f3->$type . "/" . $className . ".php");
	}

  }