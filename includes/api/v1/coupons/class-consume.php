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
  	class RA_Coupon_Consume {
          public static function listen(){
            return rest_ensure_response( 
                self::create_coupon()
            );
          }
    
        public static function create_coupon(){
            global $wpdb;

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

            if (!isset($_POST['copid']) || !isset($_POST['odid']) ) {
                return array(
                    "status" => "unknown",
                    "status" => "Please contact your administrator. Request unknown",
                );
            }

            $user = self::catch_post();
            
            $wpdb->query("START TRANSACTION");

            $validate_coupon = RA_Validate_Coupon::listen();
            if ($validate_coupon['status'] == 'failed' ) {
                return array(
                    "status" => $validate_coupon['status'],
                    "message" => $validate_coupon['message']
                );
            }
            
            if ($validate_coupon['data']->ID !== $_POST['copid']) {
                return array(
                    "status" => "unknown",
                    "status" => "Please contact your administrator. Coupon id does not match!",
                );
            }
            
            $wpdb->query($wpdb->prepare("INSERT INTO ra_transaction ( `coup_id`, `order_id`, `created_by` ) VALUES ( '%s', %d, %d )", $validate_coupon['data']->ID, $user['odid'], $user['wpid'] ));
            $transaciton_id = $wpdb->insert_id;

            $update_transaction = $wpdb->query($wpdb->prepare("UPDATE  ra_transaction SET `hash_id` = SHA2($transaciton_id, 256) where ID = %d ", $transaciton_id));

            if ($transaciton_id < 1 || $update_transaction < 1) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "status" => "An error occured while submitting data to server",
                );
            }else{
                $wpdb->query("COMMIT");
                return array(
                    "status" => "failed",
                    "message" => "Data has been Added successfully",
                );
            }
        }

        public static function catch_post(){
            $curl_user = array();
            
            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['odid'] = $_POST['odid'];
            
            return $curl_user;
        }
    }