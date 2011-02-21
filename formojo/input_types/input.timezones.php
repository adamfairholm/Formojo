<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Timezone Input Type
 *
 * @package		Formojo
 * @author		Addict Add-ons Dev Team
 * @copyright	Copyright (c) 2011, Addict Add-ons
 */
class Input_timezones
{
	var $label 				= 'Timezone';

	// --------------------------------------------------------------------------
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		$this->CI->load->helper('date');
	}
	
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
		return timezone_menu( $value, '', $name);
	}

}

/* End of file input.timezone.php */