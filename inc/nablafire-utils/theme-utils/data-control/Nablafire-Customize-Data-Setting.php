<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_Customize_Setting' ) ) return NULL;

class Nablafire_Customize_Data_Setting extends WP_Customize_Setting
{
	public function __construct( $manager, $option, $args, $meta ){
		parent::__construct($manager, $option, $args);
		$this->meta = $meta; 
	}
}