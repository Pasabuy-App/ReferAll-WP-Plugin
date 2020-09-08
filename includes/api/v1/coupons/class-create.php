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

            if ( !isset($_POST['title']) || !isset($_POST['info']) || !isset($_POST['value']) || !isset($_POST['type']) || !isset($_POST['limit']) || !isset($_POST['trigger']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown.",
                );
            }

            if ( empty($_POST['title']) || empty($_POST['info']) || empty($_POST['value']) || empty($_POST['type']) || empty($_POST['limit']) || empty($_POST['trigger'])  ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            if ($_POST['type'] !== 'free_ship' && $_POST['type'] !== 'discount' && $_POST['type'] !== 'min_spend' && $_POST['type'] !== 'less'  ) {
                return array(
                    "status" => "failed",
                    "message" => "Invalid type of coupons.",
                );
            }

            if ($_POST['trigger'] !== 'signin'
            && $_POST['trigger'] !== '1st_transaction'
            && $_POST['trigger'] !== 'min_spend_1000'
            && $_POST['trigger'] !== 'min_spend_500'
            && $_POST['trigger'] !== 'min_spend_10000'
            && $_POST['trigger'] !== 'min_spend_5000'
            && $_POST['trigger'] !== 'min_spend_2500'
            && $_POST['trigger'] !== 'cash_in' ) {
                return array(
                    "status" => "failed",
                    "message" => "Invalid type of coupons.",
                );
            }

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

            $wpid = $_POST['wpid'];
            $limit = $_POST['limit'];
            $type = $_POST['type'];
            $trigger =$_POST['trigger'];

            $rev_data = array(
                'title' => trim($_POST['title']),
                'info'  => trim($_POST['info']),
                'value' => trim($_POST['value']),
                'status'=> 1
            );

            $hash = '';

            $revs_type = 'coupon';

            $wpdb->query("START TRANSACTION");

            $insert_sql =  $wpdb->prepare("INSERT INTO `$table_coupons` $table_coupons_fields VALUES ('%s', '%s', '%s', '%s', %d, %d)", $hash, $expiration_date, $trigger, $type, $limit, $wpid);

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
    }