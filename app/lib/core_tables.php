<?php
	/**
	 * Created by PhpStorm.
	 * User: betajo01
	 * Date: 9/25/16
	 * Time: 6:00 PM
     *
     * this is the core Cortex Tables file
	 */

	namespace lib;

	class core_tables extends \DB\Cortex {
        protected $fw = FALSE;
        protected
            $fieldConf = array(
            'modified_by'	=> array(
                'type'		 => 'INT4',
                'nullable'	 => false,
            ),
            'modified' => array(
                'type'     => 'TIMESTAMP',
                'nullable' => false,
            ),
            'created'		=> array(
                'type'		=> 'TIMESTAMP',
                'nullable'	=> false,
                'default'   => 'CUR_STAMP',
            ),
        );
		static public function class_test() {
			return TRUE;
		}

		public function __construct() {
            $this->fw = \Base::instance();
			parent::__construct();

			//save date record was modified.
            $this->beforesave(function($mapper){ $mapper->touch('modified'); });

            //save a user id of person who modified record.
            $this->beforesave(function($mapper){ $mapper->modified_by = $this->fw->USER_ID; });
		}

		public static function setup( $db = NULL, $table = NULL, $fields = NULL ) {
			parent::setup( $db, $table, $fields );
        }

        public function global_fields() {
            //these fields will be in every table
            return array(
            );
        }
	}