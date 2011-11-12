<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Formojo Validation Library
 *
 * Contains custom functions that cannot be used in a
 * callback method.
 *
 * @package		Formojo
 * @author		Parse19
 * @copyright	Copyright (c) 2011, Parse19
 */
class Formojo_validation extends CI_Form_validation
{
	function Formojo_validation()
	{
		$this->CI =& get_instance();
	}

	// --------------------------------------------------------------------------

	/**
	 * Check captcha callback
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function check_captcha($val)
	{
		if($this->CI->recaptcha->check_answer(
						$this->CI->input->ip_address(),
						$this->CI->input->post('recaptcha_challenge_field'),
						$val)):
		
	    	return TRUE;
		
		else:
		
			$this->CI->formojo_validation->set_message(
						'check_captcha',
						$this->CI->lang->line('recaptcha_incorrect_response'));
			
			return FALSE;
	    
	    endif;
	}

}

/* End of file Formojo_validation.php */