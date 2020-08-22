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
  	class RA_Delete_Urlhash {

          public static function listen(){
            return rest_ensure_response( 
                RA_Delete_Urlhash::delete_urlhash()
            );
          }
    
        public static function delete_urlhash(){
           
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

            if ( !isset($_POST['hash']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown.",
                );
            }

            if ( empty($_POST['hash']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            $wpid = $_POST['wpid'];

            $hash = $_POST['hash'];

            $revs_type = 'urlhash';
            
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
                    "message" => "This url is already inactive",
                );
            }

            $rev_sql = $wpdb->prepare("INSERT INTO `$table_revision` $table_revision_fields VALUES ('%s', %d, '%s', %d, %d)", $revs_type, $select_q->id, 'status', 0, $wpid);
            
            $rev_result = $wpdb->get_row( $rev_sql , OBJECT );

            
            if ($wpdb->insert_id < 0) {
                return array(
                    "status" => "error",
                    "message" => "An error occured while submitting data to the server.",
                );
            }

            return array(
                "status" => "success",
                "message" => "Data has been deleted successfully.",
            );

       
        }



    }