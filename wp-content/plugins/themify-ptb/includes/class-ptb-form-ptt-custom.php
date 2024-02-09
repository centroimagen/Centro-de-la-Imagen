<?php

class PTB_Form_PTT_Custom extends PTB_Form_PTT_Them {

	public static $prefix = 'custom_';

	public function add_fields( $data = array() ) {}

	/**
	 * Get a PTB Template name, checks whether it is a Custom template
	 *
	 * @return bool
	 */
	public static function is_custom_template( $name ) {
		return substr( $name, 0, strlen( self::$prefix ) ) === self::$prefix;
	}
}