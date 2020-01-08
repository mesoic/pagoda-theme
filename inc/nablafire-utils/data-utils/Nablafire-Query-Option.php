<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Nablafire_Query_Option
{
	// This class contains the methods which are used to expand pagoda's options 
	// tables. It has been externalized as a class in order to avoid unnecessary
	// code duplication. These methods will be called by all theme classes
	//
	// Pagoda's options tables are stored in the json file pagoda-optionstable.json
	// This is decoded by the top-level class, and passed here as defauts. We can 
	// then access defaults by passing keys to this array. 
	public function __construct($defaults, $query_tables = true)
	{
		$this->defaults = $defaults;
		$this->group    = false;

		// This allows us to override get_option() and only query 
		// the defaults table (if $query_table === false). 
		$this->query_tables = $query_tables; 
	}
	
	// This will append a value to all options in a subtable (recursive)
	public function iterate_subtable( $_subtable , $id ='' ) {

		$_iterated = array();

		foreach ( $_subtable as $key => $value ) {
		
			if ( is_array( $value ) ){ 
				$_iterated[ $key ] = $this->iterate_subtable($value, $id); 
			}
			else {
				$_key = strcmp(substr($key,-1),"_") ? $key : $key.$id ;
				$_iterated[ $_key ] = $value;
			}
		}
		return $_iterated;
	}

	// Method to get subtable this will handle all cases.
	public function subtable($keys, $iterate = false, $recursive = false){
		

		$_subtable = $this->defaults;
		foreach ($keys as $_ => $key){
			$_subtable = array_key_exists($key, $_subtable) ? $_subtable[$key] : false;
    	}

    	// Base case, no subtable found
    	if ($_subtable === false){ echo "Query Error: Subtable not found \n"; return false; }

    	// Uniterated case. Simply return the subtable in question
    	else if ($iterate === false){ return $_subtable; }

		//Iterated case. This will always expand against an array
    	else if ( is_array( $iterate ) ){
    	
    		$_iterated = array();
			foreach ($iterate as $_ => $id) {
				$_iterated[$id] = $this->iterate_subtable($_subtable, $id);
			}	
			return $_iterated;
    	}

    	// Expand append case. Performs "iterate" but for a single key 
    	else if ( is_string($iterate) || is_int($iterate) ){
    		return $this->iterate_subtable($_subtable, $iterate);
    	}

    	//Bad Iterator
    	else { echo "Type Error: Iterator must be array, int, or string \n"; return false; }
	}

	// This metod will retrieve an option from the array options if it is defined. 
	// otherwise it will return the value defined in the defaults array. If a key
	// is found then it will return the string 'false' Note that append allows us 
	// to append a unit to the value (e.g. px);
	public function option($options, $key, $_ = '') {

		if (!is_array( $options) ){ echo "Invald options array \n"; die(); }
		foreach( $options as $option => $default ){
			if (strpos($option, $key) !== false){
				$value = get_option($option);
				return ($value !== false && $this->query_tables !== false) ? 
					$value.$_ : $default.$_;
			}
		} // If the key not found return false  
		return "false";
	}
} // End Class
