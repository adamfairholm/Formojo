<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Formojo
 *
 * Serious Forms for MojoMotor
 *
 * @package		Formojo
 * @author		Addict Add-ons Dev Team
 * @copyright	Copyright (c) 2011, Addict Add-ons
 */
class Formojo
{
    public $addon_version = '1.0';
    
    private $addon;
    
    // Array of inputs we're dealing with
    private $inputs = array();
    
    private $core_input_types = array('text', 'textarea', 'dropdown', 'radio', 'password', 'hidden', 'checkbox', 'yesno_check');

	// --------------------------------------------------------------------------

	/**
	 * Formojo Constructor
	 *
	 * Load libraries and set config items
	 *
	 * @access	public
	 * @return	void
	 */
    public function __construct()
    {
        $this->addon =& get_instance();

		$this->addon->load->library('simpletags');
		
		$this->addon->load->library('form');
		
		$this->addon->load->helper( array('form', 'HTML5') );

		// -------------------------------------
		// We are looking for input: items
		// -------------------------------------
		
		$this->addon->simpletags->set_trigger('input:');

		// -------------------------------------
		// Get our extra input types
		// -------------------------------------

		$this->addon->load->library('type');
		
		$this->addon->type->gather_types();
    }

	// --------------------------------------------------------------------------

	/**
	 * Formojo Form Tag Function
	 *
	 * Takes tag parameters and creates form from the
	 * tag contents
	 *
	 * @access	public
	 * @param	array
	 * @retuen	string
	 */
    public function form($tag_data)
    {
    	$this->params = $tag_data['parameters'];
    	
    	// Parse the params so we can use 'em
    	$this->_parse_params();
    
		// -------------------------------------
		// Gather input data from the tags
		// -------------------------------------

		$this->tag_contents = $tag_data['tag_contents'];
		
		$parsed = $this->addon->simpletags->parse( $this->tag_contents, array(), array($this, 'parse_input') );
		
		$this->content = $parsed['content'];
		
		// -------------------------------------
		// Set Validation
		// -------------------------------------
		
		$this->addon->load->library('form_validation');
		
		$this->addon->form_validation->set_rules( $this->inputs );
		
		if( $this->addon->form_validation->run() !== FALSE ):
			
			// -------------------------------------
			// Send Emails
			// -------------------------------------
			
			$this->addon->load->library('parser');
			
			$this->addon->load->library('email');
			
			$this->_send_emails( '1' );

			$this->_send_emails( '2' );
			
			// Return to the right place
			redirect( $this->params['return_url'] );
		
		else:

			// -------------------------------------
			// Set Errors
			// -------------------------------------

			$this->_set_errors();

			// -------------------------------------
			// Set Singular Data
			// -------------------------------------

			$this->_set_singular_data();

			// -------------------------------------
			// Wrap in Form tags
			// -------------------------------------
			
			$hidden = array('formojo_form_submitted' => 'yes');
			
			$this->content = form_open( current_url(), '', $hidden ) . $this->content . '</form>';
	
			// -------------------------------------
			// Return the Content
			// -------------------------------------
		
			return $this->content;
			
		endif;
    }

	// --------------------------------------------------------------------------
    
    /**
     * Parses an input
     *
     * @access	public
     * @param	array
     * @return	string
     */
    public function parse_input( $tag_data )
    {
    	$attr = $tag_data['attributes'];
    	$type = $tag_data['full_segments'];
    	
    	// Make sure that the type is valid
    	if( !in_array($type, $this->core_input_types) && !isset($this->addon->type->types->$type) ):
    	
    		return "Invalid Type";
    	
    	endif;
    	
    	// We have a valid type, let's make the input
    	$input = $this->addon->form->create_input( $type, $attr, $tag_data['content'] );
    	
    	// Save the inputs for validation
    	$this->inputs[] = array(
    		'field'		=> $this->addon->form->name,
    		'label'		=> $this->addon->form->label,
    		'rules'		=> $this->addon->form->validation
    	);
    	
    	// Return the input we created
    	return $input;
    }

	// --------------------------------------------------------------------------

	/**
	 * Set Errors
	 *
	 * Display errors within the form
	 *
	 * @access	private
	 * @return	void
	 */
	private function _set_errors()
	{
		// -------------------------------------
		// Run through errors and get 'em
		// in there
		// -------------------------------------
		
		foreach( $this->inputs as $input ):
		
			$this->content = str_replace("{".$input['field']."_error}", form_error( $input['field'] ), $this->content);
		
		endforeach;
	}

	// --------------------------------------------------------------------------

	/**
	 * Set Singular Data
	 *
	 * Replaces specific {tags} with their data
	 *
	 * @access	public
	 * @return	void
	 */
	function _set_singular_data()
	{
		// -------------------------------------
		// Add a submit button
		// -------------------------------------
		
		$this->content = str_replace("{submit}", form_submit('submit_button', 'Submit'), $this->content);
		
		// -------------------------------------
		// Add Recaptcha (if nedded)
		// -------------------------------------

		if( $this->params['use_recaptcha'] == 'yes' ):

			$this->content = str_replace("{recaptcha}", $this->addon->recaptcha->get_html(), $this->content);
		
		endif;
	}

	// --------------------------------------------------------------------------

	/**
	 * Parse params and set defaults
	 *
	 * @access	private
	 * @return	void
	 */
	private function _parse_params()
	{
		// Set some param defaults

		$this->_param('form_class', 'site_form');
		
		$this->_param('use_recaptcha', 'no');

		$this->_param('return_url', current_url());

		$this->_param('notify1');

		$this->_param('notify1_layout');

		$this->_param('notify1_subject', $this->addon->site_model->get_setting('site_name') . ' Form Submission');

		$this->_param('notify1_from');

		$this->_param('notify2');

		$this->_param('notify2_layout');

		$this->_param('notify2_subject', $this->addon->site_model->get_setting('site_name') . ' Form Submission');

		$this->_param('notify2_from');
		
		// -------------------------------------
		// Set up ReCaptcha
		// -------------------------------------
		
		if( $this->params['use_recaptcha'] == 'yes' ):
		
			if( !isset($this->params['public_key']) || isset($this->params['private_key']) ):
			
				// TODO
				// Show missing keys error.
			
			endif;

		    $this->addon->config->load('recaptcha');
			
			$this->addon->load->library('recaptcha');
			
			$this->inputs[] = array(
				      'field' => 'recaptcha_response_field',
				      'label' => 'lang:recaptcha_field_name',
				      'rules' => 'required|callback_check_captcha'
    		);
    		
    		$this->addon->config->set_item('public', $this->params['public_key']);
    		$this->addon->config->set_item('private', $this->params['private_key']);
		
		endif;
	}

	// --------------------------------------------------------------------------

	/**
	 * Set Param with a default value
	 *
	 * @access	private
	 * @param	string
	 * @param	[string]
	 * @return	void
	 */
	private function _param( $param, $default = '' )
	{
		if( !isset($this->params[$param]) ):
		
			$this->params[$param] = $default;
		
		endif;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Send Emails
	 *
	 * Sends emails for a notify group
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */
	private function _send_emails( $notify )
	{
		// -------------------------------------
		// Get e-mails. Exit if there are none
		// -------------------------------------

		$emails = $this->params['notify'.$notify];
		
		$emails = explode("|", $emails);
		
		if( count($emails) == 1 && trim($emails[0]) == '' ):
			
			return;
		
		endif;
		
		// -------------------------------------
		// Parse Email Layout
		// -------------------------------------
		
		if( !$this->params["notify$notify"."_layout"] ):
		
			show_error('No layout specified for notify'.$notify);
		
		endif;

		$db_obj = $this->addon->db->limit(1)->where('layout_name', $this->params["notify$notify"."_layout"])->get('layouts');
		
		if( $db_obj->num_rows() == 0 ):
		
			show_error('Could not find '.$this->params["notify$notify"."_layout"].' layout');
		
		endif;
		
		$layout = $db_obj->row();
		
		$layout->layout_content = $this->addon->parser->parse_string($layout->layout_content, $_POST, TRUE);
		
		// -------------------------------------
		// Set From
		// -------------------------------------

		if( $this->params["notify$notify"."_from"] != '' ):
		
			$email_pieces = explode("|", $this->params["notify$notify"."_from"]);
			
			if( isset($email_pieces[1]) ):
		
				$this->addon->email->from( $email_pieces[0], $email_pieces[1] );
			
			else:
			
				$this->addon->email->from( $email_pieces[0] );
			
			endif;
			
		endif;

		// -------------------------------------
		// Set Data
		// -------------------------------------
		
		$this->addon->email->to( $emails ); 
		$this->addon->email->subject( $this->params["notify$notify"."_subject"] );
		$this->addon->email->message( $layout->layout_content );
		
		// -------------------------------------
		// Send & Clear
		// -------------------------------------

		$this->addon->email->send();
		
		$this->addon->email->clear();		
	}

}

/* End of file formojo.php */