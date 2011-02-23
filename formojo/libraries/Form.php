<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Form Library
 *
 * @package		Formojo
 * @author		Addict Add-ons Dev Team
 * @copyright	Copyright (c) 2011, Addict Add-ons
 */
class Form
{
	function __construct()
	{
		$this->mm =& get_instance();
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an input
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
			case 'yesno_check':
				return $this->yesno_check_input();
				break;
			case 'checkbox':
				return $this->_parse_multiple_input( $content );
				break;
			case 'dropdown':
				return $this->_parse_multiple_input( $content );
				break;
			case 'radio':
				return $this->_parse_multiple_input( $content );
				break;
		}

		// Wait, is this a custom input type?
		if( is_object($this->mm->type->types->$type) && method_exists($this->mm->type->types->$type, 'form_output') ):
			
			// It is. Return the form_output function
			return $this->mm->type->types->$type->form_output( $this->name, $this->value );
		
		endif;
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
		// -------------------------------------
		// See if this is a custom type
		// -------------------------------------
		
		$type = $this->type;

		isset($this->mm->type->types->$type) ? $is_custom = TRUE : $is_custom = FALSE;

		// -------------------------------------
		// Extract Attributes for Convenience
		// -------------------------------------

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
		// Checks to see if a lable has been
		// given. Finds the next best thing if
		// it hasn't.
		// -------------------------------------
	
		if( !isset($label) ):
		
			if( $is_custom && isset($this->mm->type->types->$type->label) ):
				
				// Use the label from the type as a fallback.
				$this->label = $this->mm->type->types->$type->label;
			
			else:
			
				// Last ditch. Try to make a label if there isn't one
				$this->label = ucwords($this->name);
			
			endif;
		
		else:
	
			$this->label	= $label;

		endif;

		// -------------------------------------
		// Set Validation
		// -------------------------------------
	
		$this->validation_array = array("trim");

		// If required, set it.
		if( $this->required == 'yes' ):
		
			$this->validation_array[] = "required";			
		
		endif;
		
		// Clean up the validation if they specified it.
		
		if( isset($validation) ):
	
			$validation = explode( "|", $validation );
			
			foreach( $validation as $valid ):
			
				$valid = trim($valid);
			
				if( $valid != '' ):
			
					$this->validation_array[] = $valid;
			
				endif;
			
			endforeach;
					
		endif;

		// See if there are any custom ones for a type
		
		if( $is_custom && isset($this->mm->type->types->$type->validation) ):
		
			$custom_validation = explode("|", $this->mm->type->types->$type->validation);
		
			foreach( $custom_validation as $custom_valid ):
			
				$custom_valid = trim($custom_valid);
			
				if( $custom_valid != '' ):
			
					$this->validation_array[] = $custom_valid;
			
				endif;
			
			endforeach;
					
		endif;

		// Create a validation string
		$this->validation = implode("|", $this->validation_array);

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
	 * Create a single checkbox input
	 *
	 * @access	public
	 * @return	string
	 */	
	public function yesno_check_input()
	{
		$selected = FALSE;
		
		// The value is set to 'yes' by default and there
		// is nothing submitted via the form.
		if( $this->value == 'yes' && $this->mm->input->post('formojo_form_submitted') != 'yes' ):
		
			echo 'one';
			$selected = TRUE;
			
		// The form has been submitted and the value is 'yes'
		elseif( $this->mm->input->post('formojo_form_submitted') == 'yes' && $this->mm->input->post($this->name) == 'yes' ):
		
			echo 'two';
			$selected = TRUE;
		
		endif;
	
		return form_checkbox( $this->name, 'yes', $selected );
	}

	// --------------------------------------------------------------------------

	/**
	 * Parse multiple inputs for dropdown/checkbox/radio
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */	
	function _parse_multiple_input( $content )
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
		
		// Wrap <select> stuff
		if( $this->type == 'dropdown' ):
		
			$parsed['content'] = '<select name="'.$this->name.'">'.$parsed['content'].'</select>';
		
		endif;

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
	
			return form_checkbox( $this->name, $tag_data['attributes']['value'], $selected );
			
		elseif( $this->type == 'dropdown' ):
		
			$selected_code = '';
			
			if( $selected ):
			
				$selected_code = ' selected="yes"';
			
			endif;
		
			return '<option value="'.$tag_data['attributes']['value'].'"'.$selected_code.'>'.$tag_data['attributes']['value'].'</option>'."\n";
		
		elseif( $this->type == 'radio' ):
		
			return form_radio( $this->name, $tag_data['attributes']['value'], $selected );
		
		endif;
	}

}

/* End file form.php */