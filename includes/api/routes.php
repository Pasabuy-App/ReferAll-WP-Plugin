<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/** 
        * @package referall-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/

    //Require the USocketNet class which have the core function of this plguin. 
    
    //Url Hashing
    require plugin_dir_path(__FILE__) . '/v1/referrals/class-create.php'; 
    require plugin_dir_path(__FILE__) . '/v1/referrals/class-validate.php'; 

    //Visits
    require plugin_dir_path(__FILE__) . '/v1/visits/class-insert.php'; 

    //Coupons
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-create.php'; 
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-validate.php'; 
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-delete.php'; 


    require plugin_dir_path(__FILE__) . '/v1/class-globals.php'; // globals
	
	// Init check if USocketNet successfully request from wapi.
    function referall_route()
    {
        /*
         * REFERRALS RESTAPI
        */
            register_rest_route( 'referall/v1/urlhash', 'create', array(
                'methods' => 'POST',
                'callback' => array('RA_Referrals_Create','listen'),
            ));

            register_rest_route( 'referall/v1/urlhash', 'validate', array(
                'methods' => 'POST',
                'callback' => array('RA_Validate_Referral','listen'),
            ));

        /*
         * VISITS RESTAPI
        */
            register_rest_route( 'referall/v1/visits', 'insert', array(
                'methods' => 'POST',
                'callback' => array('RA_Url_Visits','listen'),
            ));
            
        /*
         * COUPONS RESTAPI
        */
            register_rest_route( 'referall/v1/coupons', 'create', array(
                'methods' => 'POST',
                'callback' => array('RA_Coupon_Create','listen'),
            ));

            register_rest_route( 'referall/v1/coupons', 'validate', array(
                'methods' => 'POST',
                'callback' => array('RA_Validate_Coupon','listen'),
            ));

            register_rest_route( 'referall/v1/coupons', 'delete', array(
                'methods' => 'POST',
                'callback' => array('RA_Delete_Coupon','listen'),
            ));

            
    }
    add_action( 'rest_api_init', 'referall_route' );
