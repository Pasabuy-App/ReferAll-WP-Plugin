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
  	class RA_Validate_Urlhash {

        public static function listen(){
            return rest_ensure_response( 
                RA_Validate_Urlhash::validate_referral()
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

            if ( !isset($_POST['exp']) || !isset($_POST['type'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown.",
                );
            }

            if ( empty($_POST['exp']) || empty($_POST['type']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            if ( !($_POST['type'] === 'registration') ) {
                return array(
                            "status" => "failed",
                            "message" => "Invalid value for  type.",
                );
            }
         

            $wpid = $_POST['wpid'];

            $hash = $_POST['hash'];

            $date = RA_Globals:: get_user_date($wpid);
            
            $hash_sql = $wpdb->prepare("SELECT `type`, url.`id`, url.`expiry`,
            (SELECT `child_val` FROM ra_revisions WHERE revs_type = 'urlhash' AND child_key = 'status' AND parent_id = url.ID
                AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'urlhash' AND child_key = 'status' AND parent_id = url.ID)) as status
             FROM `$table_urlhash` url
             INNER JOIN $table_revision rev ON rev.parent_id = url.id
             WHERE `hash` = '%s';", $hash);
            
            $select_q = $wpdb->get_row( $hash_sql , OBJECT );

            if (!$select_q) {
                return array(
                    "status" => "failed",
                    "message" => "This url does not exists",
                );
            }

            if ($select_q->status == 0) {
                return array(
                    "status" => "failed",
                    "message" => "This url is currently inactive",
                );
            }
            
            $date_expiry = strtotime($select_q->expiry);
            $now = strtotime($date);
                    
            if ( $date_expiry < $now ) {
                return array(
                    "status" => "failed",
                    "message" => "This url is already expired",
                );
            }

            if ($select_q->type == 'registration') {
 
                //PENDING : Do something to the creator of this urlhash.
                //Possible options: Reward points, Discounts, Coupons, etc.

                return array(
                    "status" => "success",
                    "data" =>  get_site_url().'/'.'wp-json/datavice/v1/user/signup'
                );
            }
        }
    }