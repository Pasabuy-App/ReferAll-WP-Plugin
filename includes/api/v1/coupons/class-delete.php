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
  	class RA_Delete_Coupon {

        public static function listen(){
            return rest_ensure_response( 
                RA_Delete_Coupon::delete_coupon()
            );
        }
    
        public static function delete_coupon(){
           
			// Initialize WP global variable
            global $wpdb;
            $table_revision = RA_REVISIONS_TABLE;
            $table_revision_fields= RA_REVISIONS_FIELDS;

            $table_coupons = RA_COUPONS_TABLE;
            $table_coupons_fields = RA_COUPONS_FIELDS;

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

            $date = RA_Globals:: get_user_date($wpid);
            
            $hash_sql = $wpdb->prepare("SELECT `id`, `expiry` FROM `$table_coupons` WHERE `hash` = '%s';", $hash);
            
            $select_q = $wpdb->get_row( $hash_sql , OBJECT );
            // return $select_q;

            if (!$select_q) {
                return array(
                    "status" => "failed",
                    "message" => "This coupon does not exists",
                );
            }
            
            if ($select_q->expiry !== NULL) {
                $date_expiry = strtotime($select_q->expiry);
                $now = strtotime($date);
                if ( $date_expiry < $now ) {
                    return array(
                        "status" => "failed",
                        "message" => "This coupon is already expired",
                    );
                }
            } 
            
            $rev_prep = $wpdb->prepare("SELECT cp.id, cp.`expiry`,
                (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'title' AND parent_id = $select_q->id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'title' AND parent_id = $select_q->id)) as name,
                (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'info' AND parent_id = $select_q->id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'info' AND parent_id = $select_q->id)) as info,
                (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'value' AND parent_id = $select_q->id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'value' AND parent_id = $select_q->id)) as value,
                (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'status' AND parent_id = $select_q->id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'status' AND parent_id = $select_q->id)) as status
            FROM `$table_coupons` cp
            INNER JOIN `$table_revision` rev ON rev.parent_id = cp.id
            WHERE cp.`id` = %d;", $select_q->id);
            
            $coupon = $wpdb->get_row( $rev_prep , OBJECT );
            
            if ($coupon->status == 0) {
                return array(
                    "status" => "failed",
                    "message" => "This coupon is currently deactivated",
                );
            }

            return $coupon;
          
        }
    }