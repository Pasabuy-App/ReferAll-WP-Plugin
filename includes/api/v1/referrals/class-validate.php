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
  	class RA_Validate_Referral {

          public static function listen(){
            return rest_ensure_response( 
                RA_Validate_Referral::validate_referral()
            );
          }
    
        public static function validate_referral(){
           
			// Initialize WP global variable
            global $wpdb;
            $table_revision = RA_REVISIONS_TABLE;
            $table_revision_fields= RA_REVISIONS_FIELDS;

            $table_urlhash = RA_URLHASH_TABLE;
            $table_urlhash_fields = RA_URLHASH_FIELDS;

            $plugin = RA_Globals::verify_prerequisites();
            if ($plugin !== true) {

                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing.",
                );
            }

			if (DV_Verification::is_verified() == false) {
                
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues.",
                );
            }

            // if ( !isset($_POST['exp']) || !isset($_POST['type'])) {
            //     return array(
            //         "status" => "unknown",
            //         "message" => "Please contact your administrator. Request unknown.",
            //     );
            // }

            // if ( empty($_POST['exp']) || empty($_POST['type']) ) {
            //     return array(
            //         "status" => "failed",
            //         "message" => "Required fields cannot be empty.",
            //     );
            // }

            // if ( !($_POST['type'] === 'registration') ) {
            //     return array(
            //                 "status" => "failed",
            //                 "message" => "Invalid value for  type.",
            //     );
            // }
         

            $wpid = $_POST['wpid'];

            $hash = $_POST['hash'];

            $date = RA_Globals:: get_user_date($wpid);
            
            $hash_sql = $wpdb->prepare("SELECT `id`, `expiry` FROM `$table_urlhash` WHERE `hash` = '%s';", $hash);
            
            $select_q = $wpdb->get_row( $hash_sql , OBJECT );
            $date_expiry = strtotime($select_q->expiry);
            $now = strtotime($date);

            if (!$select_q) {
                return array(
                    "status" => "failed",
                    "message" => "This url does not exists",
                );
            }
            
            if ( $date_expiry < $now ) {
                return array(
                    "status" => "failed",
                    "message" => "This url is already expired",
                );
            }

            //Pending



       
        }



    }