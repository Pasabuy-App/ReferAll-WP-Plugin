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

        public static function get_timezone($wpid){
            global $wpdb;

            $result = $wpdb->get_row("SELECT
                (SELECT tzone_name FROM dv_geo_timezone WHERE country_code =   (SELECT country_code FROM dv_geo_countries WHERE ID = dv_address.country)) as time_zone
            FROM
                dv_address 
            WHERE
                wpid = $wpid");

            if (! $result  ) {
                return false;

            }else{
                return $result;

            }
        }

        public static function get_user_date($wpid){
            global $wpdb;
            $user_timezone = RA_Globals::get_timezone($wpid);
            date_default_timezone_set($user_timezone->time_zone);
            return date("Y-m-d H:i:s");

        }

        public static function get_user_ip() {
            
            if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
                if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
                    $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
                    return trim($addr[0]);
                } else {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
            } else {
                return $_SERVER['REMOTE_ADDR'];
            }
        
        }

        public static function get_user_platform(){
            if(stripos($_SERVER['HTTP_USER_AGENT'],"iPhone")){
                return 'iPhone';
            }
            if(stripos($_SERVER['HTTP_USER_AGENT'],"Android")){
                return 'Android';
            }
        }


		
	}
