<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Email Input Type
 *
 * @package		Formojo
 * @author		Adam Fairholm
 * @copyright	Copyright (c) 2011-2013, Adam Fairholm
 */
class Input_email
{
	var $label 				= 'E-mail';

	var $validation			= 'valid_email';
		
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