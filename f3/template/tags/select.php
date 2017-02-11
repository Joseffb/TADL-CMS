<?php
/**
 *	Select TagHandler
 *
 *	The contents of this file are subject to the terms of the GNU General
 *	Public License Version 3.0. You may not use this file except in
 *	compliance with the license. Any of the license terms and conditions
 *	can be waived if you get permission from the copyright holder.
 *
 *	Copyright (c) 2015 ~ ikkez
 *	Christian Knuth <ikkez0n3@gmail.com>
 *
 *	@version: 0.2.0
 *	@date: 14.04.2015
 *
 **/

namespace Template\Tags;

class Select extends \Template\TagHandler {

	function __construct() {
		/** @var \Base $f3 */
		$f3 = \Base::instance();
		if (!$f3->exists('template.form.srcKey'))
			$f3->set('template.form.srcKey','POST');
		parent::__construct();
	}

	/**
	 * build tag string
	 * @param $attr
	 * @param $content
	 * @return string
	 */
	function build($attr, $content) {
		$srcKey = \Base::instance()->get('template.form.srcKey');
		if (array_key_exists('group', $attr)) {
			$attr['group'] = $this->tmpl->token($attr['group']);
			$name = $this->tokenExport($attr['name']);
			$ar_name = preg_replace('/\'*(\w+)(\[.*\])\'*/i','[$1]$2',$name,-1,$i);
			$name = $i ? $ar_name : '['.$name.']';
			$content .= '<?php foreach('.$attr['group'].' as $key => $val) {?>'.
				$this->tmpl->build('<option value="{{@key}}"'.
						'{{(isset(@'.$srcKey.$name.') && @'.$srcKey.$name.'==@key)?'.
						'\' selected="selected"\':\'\'}}>{{@val}}</option>').
						'<?php } ?>';
			unset($attr['group']);
		}
		// resolve all other / unhandled tag attributes
		$attr = $this->resolveParams($attr);
		// create element and return
		return '<select'.$attr.'>'.$content.'</select>';
	}
}