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


		public static function encrypt_decrypt( $action, $string, $key, $iv ) {
            
            $output = false;
            $encrypt_method = "AES-256-CBC";
            // hash
            $key = hash('sha256', $key);

            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $iv), 0, 16);
            if ( $action == 'encrypt' ) {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            } 
            if( $action == 'decrypt' ) {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }
            return $output;
		}

		
	}
