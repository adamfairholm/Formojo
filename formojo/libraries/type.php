<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Type Library
 *
 * @package		Formojo
 * @author		Addict Add-ons Dev Team
 * @copyright	Copyright (c) 2011, Addict Add-ons
 */
class Type
{
    function __construct()
    {
		$this->CI =& get_instance();
		
		$this->CI->load->helper( 'directory' );
		
		$this->types = new stdClass;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Get the types together as a big object
	 *
	 * @access	public
	 * @return	void
	 */
	public function gather_types()
	{
		$addon_dir = APPPATH.'third_party/formojo/';
	
		// -------------------------------------
		// Get Core Input Helpers
		// -------------------------------------
		
		$types_files = directory_map($addon_dir.'input_helpers/');
		
		$this->_load_types( $types_files, $addon_dir.'input_helpers/' );
	
		// -------------------------------------
		// Get Third Party Input Helpers
		// -------------------------------------
		
		if( is_dir($addon_dir.'third_party/') ):

			$types_files = directory_map($addon_dir.'third_party/');
			
			if( is_array($types_files) && !empty($types_files) ):
		
				$this->_load_types( $types_files, $addon_dir.'third_party/' );
			
			endif;
		
		endif;		
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Load types
	 *
	 * @access	private
	 * @param	array
	 * @param	string
	 * @return	void
	 */	
	private function _load_types( $types_files, $addon_path )
	{
		foreach( $types_files as $type ):
	
			$items = explode(".", $type);
			
			if( $items[0] == 'input' || count($items) == 3 ):
			
				$class_name = 'Input_'.$items[1];
				
				require_once($addon_path.$type);
					
				$this->types->$items[1] = new $class_name();
				
				// Automatically add the slug.
				$this->types->$items[1]->slug = $items[1];

			endif;
								
		endforeach;
	}

}

/* End of file type.php */