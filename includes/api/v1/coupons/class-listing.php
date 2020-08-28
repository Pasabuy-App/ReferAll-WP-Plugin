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
  	class RA_Listing_Coupon {

          public static function listen(){
            return rest_ensure_response( 
                self::listen_open()
            );
          }
    
        public static function listen_open(){
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

            $table_revision = RA_REVISIONS_TABLE;
            $table_coupons = RA_COUPONS_TABLE;
         
            $sql = "SELECT
                    cp.hash_id as ID,
                    COALESCE(cp.limit - (
                                SELECT
                                    COUNT(t1.`status`) as `used`
                                FROM
                                    mp_orders t1
                                    LEFT JOIN ra_transaction t2 ON  t1.ID = t2.order_id
                                    WHERE t1.`status` = 'delivered' AND t1.status != 'canceled' AND t2.coup_id = cp.hash_id
                            )) as `limit`,	
                    cp.type,
                    cp.expiry as expiry_date,
                    cp.date_created,            
                    (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'title' AND parent_id = cp.id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'title' AND parent_id = cp.id)) as name,
                    (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'info' AND parent_id = cp.id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'info' AND parent_id = cp.id)) as info,
                    (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'value' AND parent_id = cp.id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'value' AND parent_id = cp.id)) as value,
                    IF( (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'status' AND parent_id = cp.id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'status' AND parent_id = cp.id)) = 1
                        , 'Active','Inactive')	as status
                FROM
                    $table_coupons cp
                LEFT JOIN 
                    $table_revision rev ON 
                    rev.parent_id = cp.ID
                WHERE 
                    rev.child_key = 'status' 
                    AND revs_type ='coupon'
                    AND rev.ID = (SELECT MAX(ID) FROM $table_revision WHERE parent_id = rev.parent_id AND revs_type = 'coupon'  )
            ";


            isset($_POST['copid'])? $copid = $_POST['copid']: $copid = NULL;
            isset($_POST['type'])? $tp = $_POST['type']: $tp = NULL;
            isset($_POST['status']) ? $sts = $_POST['status'] : $sts = NULL  ;

            $status = $sts == '0' || $sts == NULL ? NULL : ($sts == '2'&& $sts !== '0'? '0':'1');
            $coupon_id = $copid == '0' || $copid == NULL? $coupon_id = NULL: $coupon_id = $copid;
            $type = $tp == '0' || $tp == NULL? $type = NULL: $type = $tp;
         
            if (isset($_POST['status'])) {

                if ($status !== NULL) {
                    $sql .= " AND  (SELECT `child_val` FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'status' AND parent_id = cp.id AND id = (SELECT MAX(id) FROM $table_revision WHERE revs_type = 'coupon' AND child_key = 'status' AND parent_id = cp.id)) = '$status' ";
                }
            }

            if (isset($_POST['copid'])) {
                if ($coupon_id !== NULL) {
                    $sql .= " AND  cp.hash_id = '$coupon_id' ";
                }
            }

            if (isset($_POST['type'])) {
                if ($type !== NULL) {

                    if ($_POST['type'] !== 'free_ship' && $_POST['type'] !== 'discount' && $_POST['type'] !== 'min_spend' && $_POST['type'] !== 'less'  ) {
                        return array(
                            "status" => "failed",
                            "message" => "Invalid type of coupons.",
                        );
                    }
        
                    $sql .= " AND  cp.type = '$type' ";
                }
            }
            
            $result = $wpdb->get_results($sql );
            
            return array(
                "status" => "success",
                "data" => $result
            );
        }
    }