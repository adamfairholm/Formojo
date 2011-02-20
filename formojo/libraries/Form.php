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
	 * @param	content
	 * @return	string
	 */	
	public function create_input( $type, $attributes, $content )
	{		
		$this->type = $type;

		$content = trim($content);

		$this->parse_attributes( $attributes );
		
		switch( $type )
		{
			case 'text':
				return $this->text_input();
				break;
			case 'textarea':
				return $this->textarea_input();
				break;
			case 'hidden':
				return $this->hidden_input();
				break;
			case 'password':
				return $this->password_input();
				break;
			case 'checkbox':
				return $this->checkbox_input( $content );
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
	
		if( isset($name) ):

			$this->name		= $name;
		
		else:

			$this->name		= '';
		
		endif;		

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
				//show_error('A checkbox needs a value');
				$this->value = '';
				
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
	 * Create a hidden input
	 *
	 * @access	public
	 * @return	string
	 */	
	public function hidden_input()
	{
		$input_config = array(
              'name'        => $this->name,
              'id'          => $this->name,
              'value'       => $this->value
            );

		return form_hidden( $input_config );
	}

	// --------------------------------------------------------------------------

	/**
	 * Create a checkbox input
	 *
	 * @access	public
	 * @return	string
	 */	
	public function checkbox_input( $content )
	{
		// No options? Nothing to see here
		if( ! $content ):
			
			return;
		
		endif;

		// We are hijacking the trigger for a sec
		$this->mm->simpletags->set_trigger('option');
	
		$parsed = $this->mm->simpletags->parse( $content, array(), array($this, 'parse_options') );

		// Back to normal
		$this->mm->simpletags->set_trigger('input:');

		return $parsed['content'];
	}

	// --------------------------------------------------------------------------

	/**
	 * Parse checkbox, radio, and select options
	 *
	 * @access	public
	 * @param	array
	 * @return	string
	 */
	public function parse_options( $tag_data )
	{
		// We need a value
		if( !isset($tag_data['attributes']['value']) ):
		
			show_error("Missing a $this->type value.");
		
		endif;
	
		// To check the post we need to remove the [] if
		// they indeed do exist
		$post_name = str_replace('[]', '', $this->name);
	
		// Is it checked?
		
		$selected = FALSE;
		
		if( $this->mm->input->post($post_name) ):
		
			// Could be array or no.
			
			if( is_array($this->mm->input->post($post_name)) && in_array($tag_data['attributes']['value'], $this->mm->input->post($post_name)) ):
				
				$selected = TRUE;
			
			elseif( !is_array($this->mm->input->post($post_name)) && $this->mm->input->post($post_name) == $tag_data['attributes']['value'] ):
			
				$selected = TRUE;
			
			endif;
		
		else:
		
			if( isset($tag_data['attributes']['selected']) && $tag_data['attributes']['selected'] == 'yes' ):
			
				$selected = TRUE;
						
			endif;
		
		endif;
		
		if( $this->type == 'checkbox' ):
	
			return form_checkbox($this->name, $tag_data['attributes']['value'], $selected);
		
		endif;
	}

}

/* End file Form.php */