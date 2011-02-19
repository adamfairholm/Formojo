<?php

class Formojo
{
    public $addon_version = '1.0';
    
    private $addon;
    
    // Array of inputs we're dealing with
    private $inputs = array();
    
    private $input_types = array('text', 'textarea', 'select', 'radio', 'password', 'hidden');

	// --------------------------------------------------------------------------

    public function __construct()
    {
        $this->addon =& get_instance();

		$this->addon->load->library('simpletags');
		
		$this->addon->load->library('form');
		
		$this->addon->load->helper('form');
		
		$this->addon->simpletags->set_trigger('input:');
    }

	// --------------------------------------------------------------------------

    public function form($tag_data)
    {
		// -------------------------------------
		// Gather input data from the tags
		// -------------------------------------

		$this->tag_contents = $tag_data['tag_contents'];
		
		$parsed = $this->addon->simpletags->parse( $this->tag_contents, array(), array($this, 'parse_input') );
		
		$this->content = $parsed['content'];
		
		// DEBUG
		//print_r($this->inputs);
		
		// -------------------------------------
		// Set Validation
		// -------------------------------------
		
		$this->addon->load->library('form_validation');
		
		$this->addon->form_validation->set_rules( $this->inputs );
		
		if( $this->addon->form_validation->run() !== FALSE ):
			
			
		
		else:

			// -------------------------------------
			// Set Singular Data
			// -------------------------------------

			$this->_set_singular_data();

			// -------------------------------------
			// Wrap in Form tags
			// -------------------------------------
			
			$this->content = form_open( current_url() ) . $this->content . form_close();
	
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
    	if( !in_array($type, $this->input_types) ):
    	
    		return "Invalid Type";
    	
    	endif;
    	
    	// We have a valid type, let's make the input
    	$input = $this->addon->form->create_input( $type, $attr );
    	
    	// Save the inputs for validation
    	$this->inputs[] = array(
    		'field'		=> $this->addon->form->name,
    		'label'		=> $this->addon->form->label,
    		'rules'		=> $this->addon->form->validation
    	);
    	
    	// Return the input we created
    	return $input;
    }

	function _set_singular_data()
	{
		// -------------------------------------
		// Add a submit button/
		// -------------------------------------
		
		$this->content = str_replace("{submit}", form_submit('submit_button', 'Submit'), $this->content);
	}

}

/* End of file formojo.php */