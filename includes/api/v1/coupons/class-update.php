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
/**
            if ( !isset($_POST['exp']) || empty($_POST['exp']) || $_POST['exp'] == "") {
          
                $expiration_date = NULL;
                
            } else {

                $dt = self::validateDate($_POST['exp']);   

                if ( !$dt ) {
                    return array(
                        "status" => "failed",
                        "message" => "Expiratation date is not in valid format.",
                    );
                }

                $expiration_date = $_POST['exp'];
  
            }
 */
            $wpid = $_POST['wpid'];
            
            $hash = '';

            $revs_type = 'coupon';
        


            isset($_POST['type'])? $t1 = $_POST['type']: $t1 = NULL;
            isset($_POST['limit'])? $t2 = $_POST['limit']: $t2 = NULL;
            isset($_POST['expiry'])? $t3 = $_POST['expiry']: $t3 = NULL;

            $type = $t1 == '0' || $t1 == NULL? $type = NULL: $type = $t1;
            $limit = $t2 == '0' || $t2 == NULL? $limit = NULL: $limit = $t2;
            $expiry = $t3 == '0' || $t3 == NULL? $expiry = NULL: $expiry = $t3;

            $user = self::catch_post();

            $wpdb->query("START TRANSACTION");
          

            $wpdb->query($wpdb->prepare( "UPDATE `$table_coupons` SET  `limit` = %d, `type` = %s, `expiry` = %s WHERE ID = %s ",   ));









           
            $insert_sql =  $wpdb->prepare("INSERT INTO `$table_coupons` $table_coupons_fields VALUES ('%s', '%s', '%s', %d, %d)", $hash, $expiration_date, $type, $limit, $wpid);

            $insert_q = $wpdb->get_row( $insert_sql , OBJECT );
            
            //Get last insert id
            $parent_id = $wpdb->insert_id;

            if ( empty($expiration_date) ) {
                $wpdb->query("UPDATE `$table_coupons` SET `expiry` = NULL WHERE `id` = $parent_id");

            } else {
                $wpdb->query("UPDATE `$table_coupons` SET `expiry` = '$expiration_date' WHERE `id` = $parent_id");

            }
            
            $update_sql = $wpdb->prepare("UPDATE `$table_coupons` SET `hash_id`= SHA2( '$parent_id', 256) WHERE id = %d;", $parent_id);

            $update_q = $wpdb->get_row( $update_sql , OBJECT );

            foreach ($rev_data as $key => $value) {

                $rev_sql = $wpdb->prepare("INSERT INTO `$table_revision` $table_revision_fields VALUES ('%s', %d, '%s', '%s', %d)", $revs_type, $parent_id, $key, $value, $wpid);
            
                $rev_result = $wpdb->get_row( $rev_sql , OBJECT );

                if ($wpdb->insert_id < 1) {
                    $wpdb->query("ROLLBACK");
                    return array(
                        "status" => "error",
                        "message" => "An error occured while submitting data to the server",
                    );
                }
            }
            
            if ( $parent_id < 1 ) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "error",
                    "message" => "An error occured while submitting data to the server",
                );
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