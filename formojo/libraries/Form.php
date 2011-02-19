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
		$this->parse_attributes( $attributes );
		
		switch( $type )
		{
			case 'text':
				return $this->text_input();
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
              'value'       => ''
            );

		return form_input( $input_config );
	}

}

/* End file Form.php */