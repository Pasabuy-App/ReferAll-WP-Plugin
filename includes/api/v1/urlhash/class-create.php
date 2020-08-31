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
  	class RA_Urlhash_Create {
          public static function listen(){
            return rest_ensure_response( 
                RA_Urlhash_Create::create_urlhash()
            );
          }
    
        public static function create_urlhash(){
            
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

            if ( !isset($_POST['type']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown.",
                );
            }

            if ( empty($_POST['type']) ) {
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

            if ( isset($_POST['exp']) || !empty($_POST['exp']) ) {
                
                $expiration_date = $_POST['exp'];

                $dt = TP_OrdersByDate::validateDate($_POST['exp']);   
          
                if ( !$dt ) {
                    return array(
                            "status" => "failed",
                            "message" => "Expiratation date is not in valid format.",
                    );
                }
            } else {

                $expiration_date = NULL;

            }

            

            $wpid = $_POST['wpid'];

            $type = $_POST['type'];

            $hash = '';
        
            $wpdb->query("START TRANSACTION");

            $revs_type = 'urlhash';

            //Insert into table urlhash
            $insert_sql =  $wpdb->prepare("INSERT INTO `$table_urlhash` $table_urlhash_fields VALUES ('%s', '%s', '%s', %d)", $type, $hash, $expiration_date, $wpid);

            $insert_q = $wpdb->get_row( $insert_sql , OBJECT );

            //Get last insert id
            $parent_id = $wpdb->insert_id;

            if ( empty($expiration_date) ) {
                $wpdb->query("UPDATE `$table_urlhash` SET `expiry` = NULL WHERE `id` = $parent_id");
            } else {
                $wpdb->query("UPDATE `$table_urlhash` SET `expiry` = '$expiration_date' WHERE `id` = $parent_id");
            }
            
            $rev_sql = $wpdb->prepare("INSERT INTO `$table_revision` $table_revision_fields VALUES ('%s', %d, '%s', %d, %d)", $revs_type, $parent_id, 'status', 1, $wpid);
            
            $rev_result = $wpdb->get_row( $rev_sql , OBJECT );
           
            $update_sql = $wpdb->prepare("UPDATE `$table_urlhash` SET `hash`=concat(
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

            $hash_sql = $wpdb->prepare("SELECT `hash` FROM `$table_urlhash` WHERE `ID` = %d;", $parent_id);
            
            $select_q = $wpdb->get_row( $hash_sql , OBJECT );

            // $short_url = wp_normalize_path(ABSPATH. '/') . $select_q->hash;
            $short_url = get_site_url().'/'. $select_q->hash;
            
            if ( $parent_id < 1 ) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "error",
                    "message" => "An error occured while submitting data to the server",
                );

            }else{
                $wpdb->query("COMMIT");

                return array(
                    "status" => "success",
                    "data" => $short_url,
                );
            }
        }

        public static function validateDate($date, $format = 'Y-m-d h:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
    }