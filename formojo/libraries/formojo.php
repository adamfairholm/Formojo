<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Formojo
 *
 * Serious Forms for MojoMotor
 *
 * @package		Formojo
 * @author		Parse19
 * @copyright	Copyright (c) 2011, Parse19
 */
class Formojo
{
    public $addon_version = '1.0.3';
    
    private $addon;
    
    // Array of inputs we're dealing with
    private $inputs = array();
    
    private $core_input_types = array('text', 'textarea', 'dropdown', 'radio', 'password', 'hidden', 'checkbox', 'yesno_check');

	/**
	 * Formojo Constructor
	 *
	 * Load libraries and set config items
	 *
	 * @return	void
	 */
    public function __construct()
    {
        $this->addon =& get_instance();
        
		$this->addon->load->library('simpletags');
		
		$this->addon->load->library('form');
		
		$this->addon->load->helper('form');
		
		include_once(APPPATH.'third_party/formojo/helpers/HTML5_helper.php');

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
		
		$this->_check_log_tables();
    }

	/**
	 * Formojo Form Tag Function
	 *
	 * Takes tag parameters and creates form from the
	 * tag contents
	 *
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
			
			$this->filtered_input = $this->_filter_post_data();
			
			$this->_send_emails( '1' );

			$this->_send_emails( '2' );
			
			$this->_log_input();
			
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
			
			$attr['class'] = $this->params['form_class'];
			
			if($this->params['form_id'] != ''):
			
				$attr['id'] = $this->params['form_id'];
			
			endif;
			
			$this->content = form_open( current_url(), $attr, $hidden ) . $this->content . '</form>';
	
			// -------------------------------------
			// Return the Content
			// -------------------------------------
		
			return $this->content;
			
		endif;
    }
    
    /**
     * Parses an input
     *
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

	/**
	 * Set Errors
	 *
	 * Display errors within the form
	 *
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

	/**
	 * Set Singular Data
	 *
	 * Replaces specific {tags} with their data
	 *
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

	/**
	 * Parse params and set defaults
	 *
	 * @return	void
	 */
	private function _parse_params()
	{
		// Set some param defaults

		$this->_param('pre_error', '<span class="error">');

		$this->_param('post_error', '</span>');

		$this->_param('form_class', 'site_form');

		$this->_param('form_id', '');

		$this->_param('success_message', '<p class="success">Form submitted successfully</p>');
		
		$this->_param('use_recaptcha', 'no');

		$this->_param('return_url', current_url());

		$this->_param('notify1');

		$this->_param('notify1_layout');

		$this->_param('notify1_subject', $this->addon->site_model->get_setting('site_name').' Form Submission');

		$this->_check_notify_from('notify1_from');

		$this->_param('notify2');

		$this->_param('notify2_layout');

		$this->_param('notify2_subject', $this->addon->site_model->get_setting('site_name').' Form Submission');

		$this->_check_notify_from('notify2_from');
				
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

	/**
	 * Parse notify from value
	 *
	 * @param	string - notify indicator string (ie 'notify2_from')
	 * @return	string
	 */
	private function _check_notify_from($notify_string)
	{
		if(isset($this->params[$notify_string])):
		
			$val = $this->params[$notify_string];
		
		else:
		
			return;
		
		endif;
	
		// No scrubs
		if(trim($val) == '') return;
	
		// Check to see if we have no email address and a post input.
		if(strpos($val, '@') === FALSE and $this->addon->input->post($val)):
		
			// Return the actual value from the form
			$this->params[$notify_string] = $this->addon->input->post($val);
		
		endif;
	}

	/**
	 * Set Param with a default value
	 *
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
	
	/**
	 * Send Emails
	 *
	 * Sends emails for a notify group
	 *
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
		
			$emails[$key] = $this->_process_email_address($piece);
		
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
				
		$layout->layout_content = $this->addon->parser->parse_string($layout->layout_content, $this->filtered_input, TRUE);
		
		// -------------------------------------
		// Set From
		// -------------------------------------

		if( $this->params["notify$notify"."_from"] != '' ):
		
			$email_pieces = explode("|", $this->params["notify$notify"."_from"]);
			
			if( isset($email_pieces[1]) ):
		
				$this->addon->email->from( $this->_process_email_address($email_pieces[0]), $email_pieces[1] );
			
			else:
			
				$this->addon->email->from( $email_pieces[0] );
			
			endif;
			
		else:
			
			// Hmm. No from address. We'll just make a noreply based on the domain.
			preg_match('@^(?:http://)?([^/]+)@i', $this->addon->site_model->get_setting('site_path'), $matches);
			preg_match('/[^.]+\.[^.]+$/', $matches[1], $matches);

			$this->addon->email->from('noreply@'.$matches[0]);
			
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
			'form_data'	=> serialize($this->filtered_input)
			
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
	
	/**
	 * Process an email address - if it is not 
	 * an email address, pull it from post
	 *
	 * @param	email
	 * @return	string
	 */
	private function _process_email_address($email)
	{
		if(strpos($email, '@') === FALSE and $this->addon->input->post($email)):
		
			return $email;
			
		endif;
		
		return $email;
	}
		
	/**
	 * Check for the Log Tables
	 *
	 * Checks for our log table and creates it if
	 * it doesn't exist.
	 *
	 * @return	void
	 */
	private function _check_log_tables()
	{
		$outcome = TRUE;
	
		// Check the email log
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
						
			$outcome = $this->addon->dbforge->create_table('formojo_email_log');
			
		endif;

		// Check the contact log
		if(!$this->addon->db->table_exists('formojo_form_log')):
		
			$this->addon->load->dbforge();

			$this->addon->dbforge->add_field('id');	
			
			$structure = array(
				'created'			=> array('type' => 'DATETIME'),
				'form_data'			=> array('type' => 'LONGTEXT')
			);		
			
			$this->addon->dbforge->add_field($structure);
						
			$outcome = $this->addon->dbforge->create_table('formojo_form_log');
			
		endif;
		
		// Make sure it worked
		if(!$outcome):
		
			show_error('Failed to create necessary tables');
		
		endif;
	}
	
	/**
	 * Filter Post Data
	 *
	 * Takes the post data and formats it so that we can use the 
	 * info in emails and other places.
	 *
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

	/**
	 * Log the form input
	 *
	 * @access	public
	 * @return	void
	 */	
	function _log_input()
	{
		$insert_data['created']	= date('Y-m-d H:i:s');
		$insert_data['form_data'] = serialize($this->filtered_input);
	
		$this->addon->db->insert('formojo_form_log', $insert_data);
	}

}