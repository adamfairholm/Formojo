<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Random ID Input Helper
 *
 * Generates a hidden random ID.
 *
 * @package		Formojo
 * @author		Addict Add-ons Dev Team
 * @copyright	Copyright (c) 2011, Addict Add-ons
 */
class Input_random_id
{
	var $label 				= 'Random ID';
	
	// --------------------------------------------------------------------------
	
	/**
	 * Output form input
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function form_output($name, $value = '')
	{	
		return form_hidden($name, mt_rand());
	}

}

/* End of file input.random_id.php */