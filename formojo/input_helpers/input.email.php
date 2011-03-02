<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Email Input Type
 *
 * @package		Formojo
 * @author		Addict Add-ons Dev Team
 * @copyright	Copyright (c) 2011, Addict Add-ons
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