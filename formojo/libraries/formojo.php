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
    public $addon_version = '0.8';
    
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

		// -------------------------------------
		// Make sure we have our email log table
		// -------------------------------------
		
		$this->_check_log_table();
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
	 * @return	string
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
		
		$this->addon->load->library('formojo_validation');
		
		$this->addon->formojo_validation->set_rules( $this->inputs );

		// -------------------------------------
		// Set error delimiters
		// -------------------------------------
		
		$this->addon->formojo_validation->set_error_delimiters($this->params['pre_error'], $this->params['post_error']);
		
		if( $this->addon->formojo_validation->run() !== FALSE ):
		
			// -------------------------------------
			// Send Emails
			// -------------------------------------
			
			$this->addon->load->library('parser');
			
			$this->addon->load->library('email');
			
			$this->_send_emails( '1' );

			$this->_send_emails( '2' );
			
			// Set message
			
			$this->addon->session->set_flashdata('success_message', $this->params['success_message']);
			
			// Return to the right place
			redirect( $this->params['return_url'] );
		
		else:

			// -------------------------------------
			// Set Errors
			// -------------------------------------

			$this->_set_errors();

			// -------------------------------------
			// Set Singular Data. reCAPTCHA, etc.
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
    		'rules'		=> $this->addon->form->validation,
    		'type'		=> $this->addon->form->type
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
		
			// Remove []
			$field_slug = str_replace('[]', '', $input['field']);
			
			$this->content = str_replace(
								"{".$field_slug."_error}",
								$this->addon->formojo_validation->error($input['field'], $this->params['pre_error'], $this->params['post_error']),
								$this->content);
		
		endforeach;
		
		// Special reCAPTCHA replace
		$this->content = str_replace(
								"{recaptcha_error}", 
								$this->addon->formojo_validation->error('recaptcha_response_field', $this->params['pre_error'], $this->params['post_error']),
								$this->content);
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

		// -------------------------------------
		// Add Success Message
		// -------------------------------------
		
		if($this->addon->session->flashdata('success_message')):
			
			$this->content = str_replace("{success_message}", $this->addon->session->flashdata('success_message'), $this->content);
		
		else:
		
			$this->content = str_replace("{success_message}", '', $this->content);

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

		$this->_param('pre_error', '<span class="error">');

		$this->_param('post_error', '</span>');

		$this->_param('form_class', 'site_form');

		$this->_param('success_message', '<p class="success">Form submitted successfully</p>');
		
		$this->_param('use_recaptcha', 'no');

		$this->_param('return_url', current_url());

		$this->_param('notify1');

		$this->_param('notify1_layout');

		$this->_param('notify1_subject', $this->addon->site_model->get_setting('site_name').' Form Submission');

		$this->_param('notify1_from');

		$this->_param('notify2');

		$this->_param('notify2_layout');

		$this->_param('notify2_subject', $this->addon->site_model->get_setting('site_name').' Form Submission');

		$this->_param('notify2_from');
				
		// -------------------------------------
		// Set up ReCaptcha
		// -------------------------------------

		$this->_param('theme', 'red');
		
		if( $this->params['use_recaptcha'] == 'yes' ):
		
		    $this->addon->config->load('recaptcha');
			
			$this->addon->load->library('recaptcha');
			
			// Set some configs
			$this->addon->recaptcha->_rConfig['public'] 	= $this->params['public_key'];
			$this->addon->recaptcha->_rConfig['private']	= $this->params['private_key'];
			$this->addon->recaptcha->_rConfig['theme']		= $this->params['theme'];
			
			$this->inputs[] = array(
				      'field'	=> 'recaptcha_response_field',
				      'label' 	=> 'lang:recaptcha_field_name',
				      'rules' 	=> 'required|check_captcha',
				      'type'	=> 'recaptcha'
    		);
		
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
		// See if we have any form items
		// -------------------------------------

		foreach($emails as $key => $piece):
		
			// Is this not an email? and it is a form item?
			if(strpos($piece, '@') === FALSE and $this->addon->input->post($piece)):
			
				// Replace it
				$emails[$key] = $this->addon->input->post($piece);
			
			endif;
		
		endforeach;
		
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
		
		$form_data = $this->_filter_post_data();
				
		$layout->layout_content = $this->addon->parser->parse_string($layout->layout_content, $this->_filter_post_data(), TRUE);
		
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
		// Send, Log & Clear
		// -------------------------------------

		$this->addon->email->send();

		$log_data = array(
			'created' 	=> date('Y-m-d H:i:s'),
			'subject'	=> $this->params["notify$notify"."_subject"],
			'debug'		=> $this->addon->email->print_debugger(),
			'form_data'	=> serialize($form_data)
			
		);
		
		// Emails. Either single or serialized array
		// of emails sent to.
		if(is_array($emails)):
		
			$log_data['to']	= serialize($emails);

		else:
		
			$log_data['to']	= $emails;
		
		endif;

		$this->addon->db->insert('formojo_email_log', $log_data);

		$this->addon->email->clear();			
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Check for the Log Tables
	 *
	 * Checks for our log table and creates it if
	 * it doesn't exist.
	 *
	 * @access	private
	 * @return	void
	 */
	private function _check_log_table()
	{
		if(!$this->addon->db->table_exists('formojo_email_log')):
		
			$this->addon->load->dbforge();

			$this->addon->dbforge->add_field('id');	
			
			$structure = array(
				'created'			=> array('type' => 'DATETIME'),
				'subject'			=> array('type' => 'VARCHAR', 'constraint' => '200'),
				'to'				=> array('type' => 'VARCHAR', 'constraint' => '200'),
				'debug'				=> array('type' => 'LONGTEXT'),
				'form_data'			=> array('type' => 'LONGTEXT')
			);		
			
			$this->addon->dbforge->add_field($structure);
						
			$this->addon->dbforge->create_table('formojo_email_log');
			
		endif;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Filter Post Data
	 *
	 * Takes the post data and formats it so that we can use the 
	 * info in emails and other places.
	 *
	 * @access	private
	 * @return	array
	 */
	private function _filter_post_data()
	{
		$form_data = $_POST;
		
		// Remove unwanted variables
	
		$unwanted = array('formojo_form_submitted', 'recaptcha_challenge_field', 'recaptcha_response_field', 'submit_button');
		
		foreach($unwanted as $field):
		
			if(isset($form_data[$field])):
			
				unset($form_data[$field]);
			
			endif;
		
		endforeach;
		
		// Add some fun new variables to use!
		$form_data['when_submitted']	= date('Y-m-d H:i:s');
		$form_data['ip_address']		= $this->addon->input->ip_address();
		
		$this->addon->load->library('user_agent');
		
		$form_data['browser']			= $this->addon->agent->browser().' '.$this->addon->agent->version();
		$form_data['platform']			= $this->addon->agent->platform();
		
		// Go through the inputs, see if there is a post for them.
		// If there is, check to see if it an array and format for array fun
		// If there is nothing, check to see if it is an yes/no, otherwise
		// just set to null
		
		$ghosters = array('');
		
		foreach($this->inputs as $input):
		
			// We do this so it works.
			$input['field'] = str_replace('[]', '', $input['field']);
		
			// We don't care 'bout reCAPTCHA
			if($input['type'] == 'recaptcha'):
			
				continue;
			
			endif;
			
			if(isset($form_data[$input['field']]) and is_array($form_data[$input['field']])):
			
				// Hold and give the array bits an array value of "value"
				$tmp = $form_data[$input['field']];
				
				unset($form_data[$input['field']]);
				
				$form_data[$input['field']] = array();
				
				foreach($tmp as $item):
				
					$form_data[$input['field']][]['value'] = $item;
				
				endforeach;
			
			elseif(!isset($form_data[$input['field']])):
			
				//Yes/No?
				if($input['type'] == 'yesno_check'):
				
					$form_data[$input['field']] = 'No';
			
				else:
				
					$form_data[$input['field']] = 'No value';
				
				endif;
			
			endif;
		
		endforeach;
		
		// Return the form data
		return $form_data;
	}

}

/* End of file formojo.php */