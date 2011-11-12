<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Email Input Type
 *
 * @package		Formojo
 * @author		Parse19
 * @copyright	Copyright (c) 2011, Parse19
 */
class Input_email
{
	var $label 				= 'E-mail';

	var $validation			= 'valid_email';
	
	// --------------------------------------------------------------------------
	
	/**
	 * Output form input
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function form_output( $name, $value = '' )
	{
		$options['name'] 	= $name;
		$options['id']		= $name;
		$options['value']	= $value;
		
		return form_email( $options );
	}

}

/* End of file input.email.php */