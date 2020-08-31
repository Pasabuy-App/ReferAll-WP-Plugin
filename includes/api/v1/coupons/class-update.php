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
  	class RA_Update_Coupons {
        
        public static function listen(){
            return rest_ensure_response( 
                self::listen_open()
            );
        }
        
        public static function  listen_open(){
            
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

            if ( !isset($_POST['title']) || !isset($_POST['info']) || !isset($_POST['value']) || !isset($_POST['copid']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown.",
                );
            }

            if ( empty($_POST['title']) || empty($_POST['info']) || empty($_POST['value']) || empty($_POST['copid']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            $wpid = $_POST['wpid'];
            $copid = $_POST['copid'];
            $revs_type = 'coupon';

            isset($_POST['type'])? $t1 = $_POST['type']: $t1 = NULL;
            isset($_POST['limit'])? $t2 = $_POST['limit']: $t2 = NULL;
            isset($_POST['expiry'])? $t3 = $_POST['expiry']: $t3 = NULL;

            $type = $t1 == '0' || $t1 == NULL? $type = NULL: $type = $t1;
            $limit = $t2 == '0' || $t2 == NULL? $limit = NULL: $limit = $t2;
            $expiry = $t3 == '0' || $t3 == NULL? $expiry = NULL: $expiry = $t3;

            $user = self::catch_post();

            $rev_data = array(
                "title"  => $user['title'],
                "info"   => $user['info'],
                "value"  => $user['value'],
                "status" => 1
            );

            $wpdb->query("START TRANSACTION");
          
            $validate_id = $wpdb->get_row($wpdb->prepare(" SELECT  hash_id  FROM ra_coupons WHERE hash_id = '%s' ", $copid ));
            if ($validate_id !== $copid) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Invalid coupon id"
                );
            }
            // update type of coupon
            if (isset($_POST['type'])) {
                if ($type !== NULL) {

                    if ($_POST['type'] !== 'free_ship' && $_POST['type'] !== 'discount' && $_POST['type'] !== 'min_spend' && $_POST['type'] !== 'less'  ) {
                        return array(
                            "status" => "failed",
                            "message" => "Invalid type of coupons.",
                        );
                    }
        
                    $update_type = $wpdb->query($wpdb->prepare("UPDATE ra_coupons `type` = '%s' WHERE hash_id = '%s' ", $type, ));
                    
                    if ($update_type < 1) {
                        $wpdb->query("ROLLBACK");
                        return array(
                            "status" => "failed",
                            "message" => "An error occurred while submitting data to server.",
                        );
                    }
                }
            }

            // Update limit of coupon
            if (isset($_POST['limit'])) {
                if ($limit !== NULL) {
                    if (!is_numeric($limit)) {
                        return array(
                            "status" => "failed",
                            "message" => "Invalid value of limit.",
                        );
                    }
                    $update_limit = $wpdb->query($wpdb->prepare("UPDATE ra_coupons `limit` = '%s' WHERE hash_id = '%s' ", $limit, ));

                    
                    if ($update_limit < 1) {
                        $wpdb->query("ROLLBACK");
                        return array(
                            "status" => "failed",
                            "message" => "An error occurred while submitting data to server.",
                        );
                    }
                }
            }

            // Update expiry of coupon
            if (isset($_POST['expiry'])) {
                if ($expiry !== NULL) {

                    $dt = self::validateDate($expiry);   
        
                    if ( !$dt ) {
                        return array(
                            "status" => "failed",
                            "message" => "Expiratation date is not in valid format.",
                        );
                    }
                    
                    $update_expiry = $wpdb->query($wpdb->prepare("UPDATE ra_coupons `expiry` = '%s' WHERE hash_id = '%s' ", $expiry, ));
                    if ($update_expiry < 1) {
                        $wpdb->query("ROLLBACK");
                        return array(
                            "status" => "failed",
                            "message" => "An error occurred while submitting data to server.",
                        );
                    }
                }
            }

            foreach ($rev_data as $key => $value) {

                $rev_sql = $wpdb->prepare("INSERT INTO `$table_revision` $table_revision_fields VALUES ('%s', %d, '%s', '%s', %d)", $revs_type, $parent_id, $key, $value, $wpid);
            
                $rev_result = $wpdb->query( $rev_sql , OBJECT );

                if (!$rev_result) {
                    $wpdb->query("ROLLBACK");
                    return array(
                        "status" => "error",
                        "message" => "An error occured while submitting data to the server",
                    );
                }
            }
            
            $wpdb->query("COMMIT");

            return array(
                "status" => "success",
                "data" => "Coupon successfully created",
            );
        }

        public static function validateDate($date, $format = 'Y-m-d h:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }

        public static function catch_post(){
            $curl_user = array();

            $curl_user['wpid'] = $_POST['wpid'];
            $curl_user['title'] = trim($_POST['title']);
            $curl_user['info'] = trim($_POST['info']);
            $curl_user['value'] = trim($_POST['value']);

            return $curl_user;
        }
    }