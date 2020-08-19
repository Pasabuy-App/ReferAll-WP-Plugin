<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) 
	{
		exit;
	}

	/** 
        * @package referall-wp-plugin
        * @version 0.1.0
	*/
	class RA_Globals {

        public static function verify_prerequisites(){

            if(!class_exists('DV_Verification') ){
                return 'DataVice';
            }
            return true;

        }

        

		
	}
