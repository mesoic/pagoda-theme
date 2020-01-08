<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Nablafire_Page_Tree
{
	public function __construct(){
		$this->pages = get_pages();
	}

	// Getter method to return page list
	public function get_pages(){
		return $this->pages;
	}

	// A method to return the page objects
 	public function get_page($pid){
 		return get_post($pid);
 	}

	// A method to print the page title
	public function get_title($pid){
		return get_post($pid)->post_title;
	}

	// Returns an array of all page IDs
	public function get_all_IDs(){

		$_ID = array();
		foreach ($this->pages as $_ => $page) {
			array_push($_ID, $page->ID);    
		}
		return $_ID;
	}

	// Return an array of all toplevel page IDs
	public function get_all_toplevel(){

		// Add the $ID = 0 to list of toplevel pages. This will be used to 
		// set up a default behaviour when rendering page headers.  

		$_ID = array(0 => 0); 
		foreach ($this->pages as $_ => $page) {
			($page->post_parent == 0) ? array_push($_ID, $page->ID) : false; 
		}

		// This is important to append - and is used to repesent pages that 
		// are not WP_Post objects (e.g. 404)
		return $_ID;
	}

	// Retrun toplevel categories
	public function get_categories(){

		return get_categories( array(
		'orderby' => 'name',
		'parent'  => 0,
		'hide_empty' => 0
		) );
	}

	// Check if a post ID is toplevel
	public function is_toplevel(){
		return (get_post($pid)->post_parent == 0) ? true : false; 
	}

	// A method to return toplevel ID (recursive). If the particular template
	// page is not within the page heirarchy then retrun 0.
	public function get_toplevel($pid){	

		$pid  = ($pid == false) ? 0 : $pid; 
		$_pid = ($pid == false) ? 0 : get_post($pid)->post_parent;
		return ( $_pid == 0 ) ? $pid : $this->get_toplevel($_pid);
	} 
}