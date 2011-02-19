<?php

class Form
{
	function __construct()
	{
		$this->mm =& get_instance();
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an inputŒ 
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	string
	 */	
	public function create_input( $type, $attributes )
	{		
		$this->type = $type;

		$this->parse_attributes( $attributes );
		
		switch( $type )
		{
			case 'text':
				return $this->text_input();
				break;
			case 'textarea':
				return $this->textarea_input();
				break;
			case 'password':
				return $this->password_input();
				break;
			case 'checkbox':
				return $this->checkbox_input();
				break;
		
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Parses Attributes for a field
	 *
	 * @access	public
	 * @return	void
	 */	
	public function parse_attributes( $attributes )
	{
		extract($attributes, EXTR_OVERWRITE);
		
		// -------------------------------------
		// Set Required
		// -------------------------------------
		
		if( !isset($required) ):
		
			$this->required = "no";
			
		else:
		
			$this->required = $required;
		
		endif;
		
		// -------------------------------------
		// Set Name
		// -------------------------------------
	
		$this->name		= $name;

		// -------------------------------------
		// Set Label
		// -------------------------------------
	
		if( !isset($label) ):
		
			// Try to make a label if there isn't one
			$this->label = ucwords($this->name);
		
		else:
	
			$this->label	= $label;

		endif;

		// -------------------------------------
		// Set Validation
		// -------------------------------------
	
		$this->validation = "trim";

		// If required, set it.
		if( $this->required == 'yes' ):
		
			$this->validation .= "|required";			
		
		endif;
		
		// Clean up the validation if they specified it.
		
		if( isset($validation) ):
	
			$validation = explode( "|", $validation );
			
			foreach( $validation as $valid ):
			
				$valid = trim($valid);
			
				if( $valid != '' && $valid != 'trim' && $valid != 'required' ):
			
					$this->validation .= "|".$valid;
			
				endif;
			
			endforeach;
					
		endif;

		// -------------------------------------
		// Set Value
		// -------------------------------------
		
		if( $this->type == 'checkbox' || $this->type == 'radio' ):

			// We should have been provided a value
			if( isset($value) ):
		
				$this->value = $value;
		
			else:
			
				// We really shouldn't try anything without
				// a value for these.
				show_error('A checkbox needs a value');
			
			endif;

		
		else:

			// Set the value for regular inputs

			if( $this->mm->input->post($this->name) ):
			
				$this->value = $this->mm->input->post($this->name);
			
			else:
			
				// Were we provided a value?
			
				if( isset($value) ):
			
					$this->value = $value;
			
				else:
				
					$this->value = '';
				
				endif;
			
			endif;

		endif;
	}

	// --------------------------------------------------------------------------

	/**
	 * Create a text input
	 *
	 * @access	public
	 * @return	string
	 */	
	public function text_input()
	{
		$input_config = array(
              'name'        => $this->name,
              'id'          => $this->name,
              'value'       => $this->value
            );

		return form_input( $input_config );
	}

	// --------------------------------------------------------------------------

	/**
	 * Create a textarea input
	 *
	 * @access	public
	 * @return	string
	 */	
	public function textarea_input()
	{
		$input_config = array(
              'name'        => $this->name,
              'id'          => $this->name,
              'value'       => $this->value
            );

		return form_textarea( $input_config );
	}

	// --------------------------------------------------------------------------

	/**
	 * Create a password input
	 *
	 * @access	public
	 * @return	string
	 */	
	public function password_input()
	{
		$input_config = array(
              'name'        => $this->name,
              'id'          => $this->name,
              'value'       => $this->value
            );

		return form_password( $input_config );
	}

	// --------------------------------------------------------------------------

	/**
	 * Create a checkbox input
	 *
	 * @access	public
	 * @return	string
	 */	
	public function checkbox_input()
	{
		return form_checkbox( $this->name, $this->value );
	}

}

/* End file Form.php */