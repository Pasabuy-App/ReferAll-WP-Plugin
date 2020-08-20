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
  	class RA_Coupon_Create {
          public static function listen(){
            return rest_ensure_response( 
                RA_Coupon_Create::create_coupon()
            );
          }
    
        public static function create_coupon(){
            
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

            if ( !isset($_POST['title']) || !isset($_POST['info']) || !isset($_POST['value'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown.",
                );
            }

            if ( empty($_POST['title']) || empty($_POST['info']) || empty($_POST['value']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            if ( !isset($_POST['exp']) || empty($_POST['exp']) || $_POST['exp'] == "") {
          
                $expiration_date = NULL;
                
            } else {

                $dt = TP_OrdersByDate::validateDate($_POST['exp']);   

                if ( !$dt ) {
                    return array(
                            "status" => "failed",
                            "message" => "Expiratation date is not in valid format.",
                    );
                }

                $expiration_date = $_POST['exp'];
            }


            $wpid = $_POST['wpid'];

            $rev_data = array('title' => trim($_POST['title']),
                              'info'  => trim($_POST['info']),
                              'value' => trim($_POST['value'])
            );

            $hash = '';

            $revs_type = 'coupon';
        
            // $wpdb->query("START TRANSACTION");

            //Insert into table urlhash
            $insert_sql =  $wpdb->prepare("INSERT INTO `$table_coupons` $table_coupons_fields VALUES ('%s', '%s', %d)", $hash, $expiration_date, $wpid);

            $insert_q = $wpdb->get_row( $insert_sql , OBJECT );
            
            //Get last insert id
            $parent_id = $wpdb->insert_id;
           
            $update_sql = $wpdb->prepare("UPDATE `$table_coupons` SET `hash`=concat(
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand($parent_id)*4294967296))*36+1, 1),
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),
                substring('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed)*36+1, 1)
              )
              WHERE id = %d;", $parent_id);

            $update_q = $wpdb->get_row( $update_sql , OBJECT );

            foreach ($rev_data as $key => $value) {

                $rev_sql = $wpdb->prepare("INSERT INTO `$table_revision` $table_revision_fields VALUES ('%s', %d, '%s', '%s', %d)", $revs_type, $parent_id, $key, $value, $wpid);
            
                $rev_result = $wpdb->get_row( $rev_sql , OBJECT );
                
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
        

    }