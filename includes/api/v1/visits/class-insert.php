<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/** 
        * @package referall-wp-plugin
		* @version 0.1.0
		* REST API for creating referrals.
	*/
  	class RA_Url_Visits {

          public static function listen(){
            return rest_ensure_response( 
                RA_Url_Visits::add_url_visits()
            );
          }
    
        public static function add_url_visits(){
           
			// Initialize WP global variable
            global $wpdb;
            $table_revision = RA_REVISIONS_TABLE;
            $table_revision_fields= RA_REVISIONS_FIELDS;

            $table_visits = RA_VISITS_TABLE;
            $table_visits_fields = RA_VISITS_FIELDS;

            $table_urlhash = RA_URLHASH_TABLE;

            $plugin = RA_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing.",
                );
            }

            if ( !isset($_POST['hash']) || !isset($_POST['mkey']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown.",
                );
            }

            if ( empty($_POST['hash']) || empty($_POST['mkey'])) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            $master_key = DV_Library_Config::dv_get_config('master_key', 123);
            
            //Check if master key matches
            if (!($master_key === $_POST['mkey'])) {
                return  array(
                    "status" => "error",
                    "message" => "Master keys does not match.",
                );
            }

            $hash = $_POST['hash'];

            $user_ip = RA_Globals::get_user_ip();

            $platform = RA_Globals::get_user_platform();

            $hash_sql = $wpdb->prepare("SELECT `id`, `expiry` FROM `$table_urlhash` WHERE `hash` = '%s';", $hash);
            
            $select_q = $wpdb->get_row( $hash_sql , OBJECT );
       
            if (!$select_q) {
                return array(
                    "status" => "failed",
                    "message" => "This url does not exists",
                );
            }

            $now = date('Y-m-d H:i:s', strtotime("now"));

            $date_expiry = date('Y-m-d H:i:s', strtotime($select_q->expiry));

            if ( $date_expiry < $now ) {
                return array(
                    "status" => "failed",
                    "message" => "This url is already expired",
                );
            }

            $prep_insert = $wpdb->prepare("INSERT INTO `$table_visits` $table_visits_fields VALUES (%d, '%s', '%s');", $select_q->id, $user_ip, $platform);
            
            $insert_result = $wpdb->get_row( $prep_insert , OBJECT );

            return array(
                "status" => "success",
                "message" => "Data has been added successfully.",
            );
            

        }

        





    }